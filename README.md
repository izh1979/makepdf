## makepdf

### Overview

Makepdf is a set of scripts and tools used for generating PDF version of
WHATWG HTML5 specification. It uses prince for generating PDF and
pdfsizeopt and some other tools for size optimization.

It consists of:
* Prince is a tool for converting HTML page to PDF document.
* `prince` is a shell-wrapper to run Prince binary with proper
  `LD_LIBRARY_PATH`.
* `makepdf` is a PHP-script for generating PDF document by using Price and
  optimizing with `pdfsizeopt`.
* `pdfsizeopt` is a tool for reducing size of PDF documents. This tool can use
  various optional modules for better PDF optimization, e.g. `pngout`, `jbig2`
  and `multivalent`. For example, HTML5 specification PDF version is optimized
  from 23.14 MiB to 8.95 MiB.
* pdfsizeopt-jbig2 is the JBIG2 image compressor. It is used as an optional
  optimizing module `jbig2` by `pdfsizeopt`.
* sam2p is a tool for converting raster (bitmap) image formats to
  PostScript/PDF format. This tool is used by `pdfsizeopt`.
* `tiff22pnm` and `png22pnm` are tools for converting images to PNM format.
  They are used by sam2p.
* `pngout` is the loseless PNG images optimizer. It is used by `pngsizeopt`.
  For more details please read [PNG-optimization.md](PNG-optimization.md)
  document.
* `whatwgpdf.php` is a script for running `makepdf` by HTTP-request.
  This script serializes generation of PDF documents because it's CPU-heavy
  process. So it's allowed to run only one instance at a time. It uses `sudo`
  to run `makepdf` under designated user.

#### How does it work?

1) Somebody sends HTTPS-request to the web-server starting `whatwgpdf.php`
   script.
2) The `whatwgpdf.php` checks that no other instance is running to reduce
   server load. If it is already running, it simply e-mails error message
   and exits.
3) If no other instance is running, the script forks and runs as a designated
   user the `makepdf` script (by using `sudo`).
4) The script runs `prince` to download HTML-document to be converted, and
   generates non-optimized version. If `prince` prints any unknown or error
   message, the generation is aborted and corresponding e-mail is sent.
5) Next the `makepdf` runs `pdfsizeopt` tool to reduce the size of generated
   document. Again, output messages are parsed for unknown strings or errors.
   If something bad happened, further processing is aborted and e-mail is
   sent.
6) Then optimized version is copied to output directory `$OUT_DIR`.
7) Then old files are removed to keep only specified number of generated
   documents to limit disk space consumption.
8) Then notification HTTPS-request is sent to `$REPORT_URL`.
9) Then e-mail about generation success is sent.

### License

All of my code is licensed under Apache v2.0 license.
All other things used in this project are licensed under their corresponding
licenses.

### Installing

#### Overview

I've tested this set of tools on openSUSE-42.3 although it should run on any
modern Linux distro. I've installed tools in `/usr/local/bin`. If you would
like to use `/usr/bin` instead you can add `--prefix=/usr` to `configure`
scripts.

#### Initializing git submodules

This project uses several git submodules. So don't forget to initialize them:
`git submodule update --init`

#### Installing Prince

First of all you need to install Prince. You can obtain it here:
https://www.princexml.com/download/

I typically use generic 64-bit version archive. You need to unpack it,
run `install.sh` and follow instructions.

For example, I've installed it in `/usr/local/lib/prince`, then copied
`prince` shell-script wrapper to `/usr/local/lib`. The purpose of this wrapper
is to set `LD_LIBRARY_PATH` environment variable pointing to Prince's library
path, e.g. to `/usr/local/lib/prince/lib`, so it could find its shared
libraries.

Then read [Finding-fonts.md](Finding-fonts.md) and
[PNG-optimization.md](PNG-optimization.md) documentation files.

#### Multivalent

Multivalent is an optional component that is used by Prince. You can get it here:
* http://multivalent.sourceforge.net/
* https://downloads.sourceforge.net/project/multivalent/multivalent/Release20091027/Multivalent20091027.jar

#### pngout

The pngout tool is used for loseless PNG-images compression. Please read
[PNG-optimization.md](PNG-optimization.md) for more information.

#### tiff22pnm and png22pnm building

You need first to install some depending libraries (with devel versions):
* liblzma5
* libjbig2
* libjpeg8
* libz1
* libtiff5

This project is a bit old and needs libpng v12. It will not build with v16.
To build the project you need to run:
```sh
./configure --with-libpng-idir=/usr/include/libpng12
sed -ri 's/-lpng/-lpng12 -lm/' cc_help.sh
make
make install
```

The `sed` script fixes library dependencies by replacing `-lpng` to `-lpng12`
to prevent linking with v16 and adding `-lm`.

#### sam2p building

First, you need to build and install `tiff22pnm` and `png22pnm` tools.

Then you need to install some depending packages and libraries (with
devel versions):
* libjpeg-turbo
* netpbm
* ghostscript

To build the tool run:
```sh
./configure
make
make install
```

#### Using separate user for PDF generation service

To make the system more secure it's recommended to run PDF generation service
tools under separate user. So you need to:
* Create a new user and a new group, e.g. `makepdf:makepdf` with `/bin/false`
  as a shell.
* Create a home for it, e.g. `/home/makepdf`. The `pngout` caching script would
  save optimized PNG in `$HOME/.pngout` directory.
* Create a record in `sudoers` allowing web-server user (e.g. `wwwrun`) to run
  PDF generation scripts under `makepdf` user. E.g. you can add following line
  to `/etc/sudoers.d/makepdf` file:
  `wwwrun ALL = (makepdf) NOPASSWD: /usr/local/bin/makepdf`

#### Logging directory

You need to create a directory where scripts could store their logs. For
example, it could be `/var/log/makepdf`. You need to make it writable for
a designated user created on previous step.

Then you need to change `$LOG_DIR` variable in `makepdf` and `whatwgpdf.php`
scripts.

Both of the scripts must use the same logging directory and the same log-file
because it is used for serializing service runs, i.e. when this file is opened
and locked, no other instance would be started.

Both of the scripts store their messages in normal case in the single
log-file. But when some errors happen (such as unexpected message from the
Prince) two additional files are created for problematic document, e.g.:
* `prince-html5-20170608024147.pdf-full.log` (full conversion log file),
* `prince-html5-20170608024147.pdf.log` (only error messages).

These files could help investigate the problem.

#### makepdf installing

You need to install `makepdf` in some folder in your `PATH` like
`/usr/local/bin`.

Then you need to edit the script to change configuration variables to suitable
values.

Don't forget to make `$OUT_DIR` writable for user `makepdf` will run as.

#### whatwgpdf.php installing

In order to start PDF generation via web-request you need to have running
web-server and install `web/whatwgpdf.php` to some accessible directory, e.g.
`/srv/www/htdocs/h/whatwgpdf.php`.

Also I would recommend to disable indexing in this directory or to install
dummy `index.html` file.

#### Setting-up PHP

In order to run `makepdf` and `whatwgpdf.php` scripts you may need to install
some additional PHP modules like php5-posix.

### pdfsizeopt optional modules compression comparison

The `pdfpsizeopt` has options for selecting optional optimization modules:
* jbig2
* pngout
* multivalent

You can change used modules in `makepdf` script via `$PDFSIZEOPT_OPTS`
variable.

Here is a comparison of how various combinations of these options effect
on optimized HTML5 specification PDF size. The original unoptimized document
generated by the Prince tool is `24 265 719` bytes long. Here are the
resulting sizes and difference to none extra optimization modules:

Size      | Modules                      | Delta
--------- | ---------------------------- | -------:
9 396 168 | None                         |        0
9 396 168 | jbig2                        |        0
9 391 219 | pngout                       |   -4 949
9 391 219 | pngout + jbig2               |   -4 949
9 281 973 | multivalent                  | -114 195
9 281 973 | jbig2 + multivalent          | -114 195
9 277 038 | pngout + multivalent         | -119 130
9 277 038 | pngout + jbig2 + multivalent | -119 130

As you may see, using of `jbig2` doesn't affect the resulting size. And the
best combination is `pngout` and `multivalent` used together. But, of course,
it depends on the document to be optimized.

### Solving problems

When something wrong happen, you can find more information in logs.
Typically there are following types of problems:
1) No font for some rare character. Read [Finding-fonts.md](Finding-fonts.md)
   for details.
2) After system update Prince is not running anymore. You can either use
   static version of Prince, or install some compat library (or put missing
   files to its `lib/` subdirectory.
3) Unknown output string from updated Prince of `pdfsizeopt`. You need to add
   it to corresponging list of recognized normal messages: `$prince_skip`,
   `$pdfsizeopt_skip` or `$pdfsizeopt_skip_regs` arrays in `makepdf` script.
4) Dead resource link in source document. Then fix your spec. :-P
5) Transitional network problems. Do nothing.

### Support

If you have questions, you can ping me by e-mail:
Igor Zhbanov <izh1979@gmail.com> or on IRC (izh@freenode).
