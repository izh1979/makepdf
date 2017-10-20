#!/bin/bash
# Written by Igor Zhbanov <izh1979@gmail.com>
#
# This script generates TTX-files with font characters maps for all
# TrueType fonts found in $FONTSDIR.

FONTDIR="/usr/share/fonts"
TTXDIR="ttx"

function generate-ttx
{
	find $1 -name "*.ttf" -exec ttx -t cmap -d . \{\} \;
}

mkdir -p "$TTXDIR"
cwd=$(pwd)
cd "$TTXDIR"

generate-ttx "$FONTDIR"

cd "$cwd"
