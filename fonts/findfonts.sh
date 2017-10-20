#!/bin/bash
# Written by Igor Zhbanov <izh1979@gmail.com>
#
# This script greps Prince tool output log for error messages about missing
# fonts like this:
#   prince: page 7: warning: no font for CJK character U+9D5C, fallback to '?'
# then trying to find fonts containing any of needed characters, sorted by
# relevance (by the number of needed characters found including variants).

if [ $# -ne 2 ]; then
	echo "Wrong arguments."
	echo "Usage: ./findfonts.sh <prince-log-file> <ttx-info-directory>"
	exit 1
fi

regexp=$(sed -r '/^.*U\+0*([0-9A-F]+),.*$/!d;s//0x\1/' $1 | sort | uniq | \
	tr "\n" "|" | sed -r 's/^(.*)\|$/\1/')
egrep -ic "($regexp)" $2/*.ttx | fgrep -v ":0" | tr ":" "\t" | sort -rn -k 2
