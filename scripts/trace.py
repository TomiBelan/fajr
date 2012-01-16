#!/usr/bin/env python

import sys
from struct import unpack
from collections import namedtuple

EntryHeader = namedtuple('EntryHeader', 'id parent length')
Entry = namedtuple('Entry', 'id parent message trace data children')

class DecodeError(Exception):
  def __init__(self, value):
    self.value = value
  def __str__(self):
    return repr(self.value)

def entry_stream(f):
  hdr = f.read(4)
  if hdr != 'FBTR':
    raise DecodeError('Bad header')
  entry = read_entry(f)
  while entry != None:
    yield entry
    entry = read_entry(f)

def read_entry(f):
  ehdr_data = f.read(12)
  if ehdr_data == '':
    # eof
    return None
  if ehdr_data[:2] != 'BE':
    raise DecodeError('Bad trace entry header')
  if ehdr_data[2:4] != 'TR':
    raise DecodeError('Unknown trace entry type')
  ehdr = EntryHeader(*unpack('>HHI', ehdr_data[4:]))
  edata = f.read(ehdr.length)
  pos, msg = unserialize(edata)
  skip, trace = unserialize(edata[pos:])
  pos += skip
  skip, data = unserialize(edata[pos:])
  return Entry(ehdr.id, ehdr.parent, msg, trace, data, [])

def unserialize(data):
  if data[0] == 'S':
    length = unpack('>I', data[1:5])[0]
    s = data[5:5+length]
    return 5+length, s
  elif data[0] == 'I':
    i = unpack('>I', data[1:5])[0]
    return 5, i
  elif data[0] == 'A':
    cnt = unpack('>I', data[1:5])[0]
    # In PHP, order of keys matters, so use array of tuples
    # instead of a map
    vals = []
    pos = 5
    for i in range(cnt):
      skip, key = unserialize(data[pos:])
      pos += skip
      skip, value = unserialize(data[pos:])
      pos += skip
      vals.append((key, value))
    return pos, vals
  elif data[0] == 'N':
    return 1, None

def build_tree(stream):
  m = {}
  entries = list(stream)
  for entry in entries:
    if entry.id in m:
      raise DecodeError('Duplicate entry with id ' + str(entry.id))
    m[entry.id] = entry
  for entry in entries:
    if not entry.parent in m:
      raise DecodeError('Unknown parent with id ' + str(entry.id))
    if entry.id != 0:
      parent = m[entry.parent]
      parent.children.append(entry)
  if not 0 in m:
    raise DecodeError('Root not present')
  return m

if __name__ == '__main__':
  entry_map = build_tree(entry_stream(sys.stdin))
  if len(sys.argv) == 1:
    def print_tree(entry, indent=1):
      print str(entry.id).zfill(4)+' '*indent + entry.message
      for child in entry.children:
        print_tree(child, indent+1)
    print_tree(entry_map[0])
  elif len(sys.argv) == 2:
    id = int(sys.argv[1])
    if not id in entry_map:
      sys.stderr.write('No such id: ' + str(id) + '\n')
      exit(1)
    entry = entry_map[int(sys.argv[1])]
    sys.stderr.write('Entry ' + str(id) + (' in ' + str(entry.parent) if entry.parent else '') + ': ' + entry.message + '\n')
    for key, value in entry.trace:
	sys.stderr.write(str(key) + ': ' + str(value) + '\n')
    if entry.data == None:
      sys.stdout.write('null\n')
    else:
      sys.stdout.write(str(entry.data))
