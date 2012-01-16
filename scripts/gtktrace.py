#!/usr/bin/env python

import trace
import datetime

import pygtk
pygtk.require('2.0')
import gtk
import pango

try:
	import gtksourceview2
except:
	gtksourceview2 = None

class TraceView(object):
	def __init__(self):
		self.window = gtk.Window(gtk.WINDOW_TOPLEVEL)
		self.window.connect("destroy", self.destroy)
		self.tree = gtk.TreeView()
		self.tree.append_column(gtk.TreeViewColumn('Message', gtk.CellRendererText(), text=1))
		self.tree.get_selection().connect('changed', self.tree_selection_changed)
		self.tree.show()
		tree_sv = gtk.ScrolledWindow()
		tree_sv.add(self.tree)
		tree_sv.set_policy(gtk.POLICY_AUTOMATIC, gtk.POLICY_AUTOMATIC)
		tree_sv.show()
		if gtksourceview2:
			self.source_view = gtksourceview2.View()
			self.source_view.set_show_line_numbers(True)
			self.language_manager = gtksourceview2.LanguageManager()
			lang = self.language_manager.guess_language(None, 'text/html')
			self.source_view.set_buffer(gtksourceview2.Buffer(lang))
		else:
			self.source_view = gtk.TextView()
		self.source_view.modify_font(pango.FontDescription('monospace'))
		self.source_view.set_editable(False)
		self.source_view.show()
		self.content_sv = gtk.ScrolledWindow()
		self.content_sv.add(self.source_view)
		self.content_sv.set_policy(gtk.POLICY_AUTOMATIC, gtk.POLICY_AUTOMATIC)
		self.content_sv.show()
		self.export_button = gtk.Button('Export')
		self.export_button.connect('clicked', self.export_button_clicked)
		self.export_button.set_sensitive(False)
		self.export_button.show()
		vbox = gtk.VBox()
		vbox.pack_start(self.content_sv)
		vbox.pack_end(self.export_button, expand=False)
		vbox.show()
		self.prop_view = gtk.TreeView()
		self.prop_view.append_column(gtk.TreeViewColumn('Name', gtk.CellRendererText(), text=0))
		self.prop_view.append_column(gtk.TreeViewColumn('Value', gtk.CellRendererText(), text=1))
		self.prop_view.show()
		prop_view_sv = gtk.ScrolledWindow()
		prop_view_sv.add(self.prop_view)
		prop_view_sv.set_policy(gtk.POLICY_AUTOMATIC, gtk.POLICY_AUTOMATIC)
		prop_view_sv.set_size_request(-1, 200)
		prop_view_sv.show()
		vpane = gtk.VPaned()
		vpane.pack1(vbox, resize=True, shrink=True)
		vpane.pack2(prop_view_sv, resize=False, shrink=True)
		vpane.show()
		hpane = gtk.HPaned()
		hpane.add1(tree_sv)
		hpane.add2(vpane)
		hpane.set_position(450)
		hpane.show()
		self.window.add(hpane)
		self.window.set_default_size(800, 600)
		self.window.set_title('Trace viewer')
		self.window.show()
		self.current_entry = None
	
	def convert_text(self, text):
		try:
			return unicode(text, 'UTF-8')
		except UnicodeDecodeError:
			return unicode(text, 'cp1250')
	
	def set_content(self, widget):
		old = self.content_sv.get_child()
		if widget == old:
			return
		if old:
			self.content_sv.remove(old)
		widget.show()
		self.content_sv.add(widget)
	
	def tree_selection_changed(self, selection, *args):
		model, it = selection.get_selected()
		if it == None:
			return
		tid = model.get_value(it, 0)
		entry = self.entry_map[tid]
		self.export_button.set_sensitive(entry.data != None)
		self.current_entry = entry
		# Zoznamy a tabulky zobrazime prehladnejsie
		if isinstance(entry.data, list):
			deep_table = True
			keys = None
			for key, value in entry.data:
				if not isinstance(value, list):
					deep_table = False
					break
				cur_keys = []
				for key2, value2 in value:
					cur_keys.append(key2)
				if keys == None:
					keys = cur_keys
				else:
					if keys != cur_keys:
						deep_table = False
			table = gtk.TreeView()
			if deep_table and keys:
				table.append_column(gtk.TreeViewColumn('Key', gtk.CellRendererText(), text=0))
				for i, key in enumerate(keys):
					table.append_column(gtk.TreeViewColumn(str(key), gtk.CellRendererText(), text=i+1))
				tablestore = gtk.ListStore(*([str]*(len(keys)+1)))
				for key, value in entry.data:
					row = [self.convert_text(str(key))]
					for key2, value2 in value:
						row.append(self.convert_text(str(value2)))
					tablestore.append(row)
				table.set_model(tablestore)
			else:
				table.append_column(gtk.TreeViewColumn('Key', gtk.CellRendererText(), text=0))
				table.append_column(gtk.TreeViewColumn('Value', gtk.CellRendererText(), text=1))
				tablestore = gtk.ListStore(str, str)
				for k, v in entry.data:
					tablestore.append((self.convert_text(str(k)), self.convert_text(str(v))))
				table.set_model(tablestore)
			self.set_content(table)
		else:
			self.source_view.get_buffer().set_text(self.convert_text(str(entry.data)))
			self.set_content(self.source_view)
		propstore = gtk.ListStore(str, str)
		for k, v in entry.trace:
			val = str(v)
			if k == 'timestamp':
				val += ' = '
				val += datetime.datetime.fromtimestamp(v).strftime('%Y-%m-%d %H:%M:%S')
			propstore.append((str(k), val))
		self.prop_view.set_model(propstore)
	
	def export_button_clicked(self, button, *args):
		filechooser = gtk.FileChooserDialog(title='Export entry data',
		  parent=self.window, action=gtk.FILE_CHOOSER_ACTION_SAVE,
		  buttons=(gtk.STOCK_CANCEL,gtk.RESPONSE_CANCEL,gtk.STOCK_SAVE,gtk.RESPONSE_OK))
		##.set_default_response(gtk.RESPONSE_OK)
		response = filechooser.run()
		if response != gtk.RESPONSE_OK:
			return
		filename = filechooser.get_filename()
		with open(filename, 'wb') as f:
			f.write(str(self.current_entry.data))
		filechooser.destroy()
	
	def loadtrace(self, from_file):
		entry_map = trace.build_tree(trace.entry_stream(from_file))
		self.entry_map = entry_map
		treestore = gtk.TreeStore(int, str)
		def add_tree(entry, parent):
			row = [entry.id, entry.message]
			it = treestore.append(parent, row)
			for child in entry.children:
				add_tree(child, it)
		add_tree(entry_map[0], None)
		self.tree.set_model(treestore)
		self.tree.expand_row((0,), False)
		
	def destroy(self, widget, data=None):
		gtk.main_quit()

if __name__ == '__main__':
	import sys
	trace_view = TraceView()
	if len(sys.argv) == 2:
		with open(sys.argv[1], 'rb') as f:
			trace_view.loadtrace(f)
	else:
		trace_view.loadtrace(sys.stdin)
	gtk.main()
