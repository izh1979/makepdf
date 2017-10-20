### HTML5 specification and missing fonts

HTML5 specification contains wide range of used characters. So it's not easy
to find needed set of fonts that would cover entire specification.

Sometimes you'll get messages like these in document convertion log:
```
prince: page 1710: warning: no font for CJK character U+9ED2, fallback to '?'
prince: page 1711: warning: no font for CJK character U+4E1D, fallback to '?'
```

It means that Prince tool couldn't find suitable font containing needed
glyphs. The Prince uses only fonts specified in its
`/usr/local/lib/prince/style/fonts.css` style configuration file.

There are two scripts that could help in this case.

### fonts/dump-fonts-ttx.sh

This script uses `ttx` tool from the `fonttools` package to extract character
map tables from all installed TrueType fonts and places it in specified
directory.

I would recommend to install all available in your Linux distro TrueType fonts
prior to running this script for better results.

### fonts/findfonts.sh

This script greps specified `makepdf` or `prince` document generation log
for missing fonts errors and greps across `*.ttx` font info files for the
fonts containing any of needed characters. The suitable fonts are sorter
by the number of characters found, so you could try to use minimal number
of new fonts to cover all needed characters.

### A set of fonts covering HTML5 specification symbols

By experimenting I've found a set of fonts that cover the entire HTML5
specification. Here is the list of openSUSE 42.3 packages (and their licenses)
containing needed fonts:
* dejavu-fonts-2.34-8.3 (SUSE-Permissive)
    - DejaVu Serif (book, italic)
* google-droid-fonts-20121204-8.1 (Apache-2.0)
    - Droid Sans Fallback (regular)
* gdouros-symbola-fonts-8.00-6.1 (SUSE-Permissive)
    - Symbola (regular)
* un-fonts-1.0.20080608-16.3 (GPL-2.0+)
    - UnBatang (regular)
    - UnDotum (regular, bold)
* vlgothic-fonts-20140801-7.1 (BSD-3-Clause and SUSE-mplus)
    - VL Gothic (regular)
* fetchmsttfonts-11.4-23.1 (MS EULA)
    - Arial (regular, bold, italic, bold italic)
    - Courier New (regular, bold, italic, bold italic)
    - Times New Roman (regular, bold, italic)

Don't forget to verify licenses whether they allow both displaying and
printing of documents. They should allow that, but it's better to doublecheck.

Also the specification uses webfont Essays1743 (bold, medium) that
is downloaded from WHATWG's site.

### Configuring Prince for using proper fonts

The patch `fonts/prince11-fonts.diff` contains changes to Prince's `fonts.css`
file that is used for fonts configuration. This file is located under Prince's
installation directory in `style` subdirectory, e.g. in
`/usr/local/lib/prince/style/fonts.css`.
