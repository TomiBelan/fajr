#!/bin/bash
cd "`dirname $0`/.."
umask 0002
git pull "$@"
./scripts/make_version.sh
./scripts/clear_cache.php
