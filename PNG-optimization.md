### PNG images optimization

To make final PDF document smaller we nned to optimize images.
Most of the images used in HTML5 specification are of type PNG. There is
a number of loseless PNG optimizers available.

Historically pngout was used because of pdfsizeopt project uses it.
But it's a bit slow and closed source. You can try to use some other
optimizer. The Leanify compresses images better and faster. And it's
open source project.

I suppose it's not hard to create a wrapper script that would use
another optimzer while converting command line arguments (or to change
psfsizeopt script).

### Installing

You can download it here:
* http://www.jonof.id.au/pngout
* http://static.jonof.id.au/dl/kenutils/pngout-20150319-linux.tar.gz

You need to unpack the archive, and copy pngout binary (for your architecture,
typically x86_64) to `/usr/local/bin/pngout-orig`. You need to rename it
because of the caching wrapper script described below.

You need to install `pngout` script from this directory to
`/usr/local/bin/pngout`.

### Optimized images caching

Because of very slow pngout and very unfrequent images change in HTML5
specification it was natural idea to implement some caching.

You can find it in the `pngout` file. The script maintains the directory
of optimized images in `$CACHEDIR="/$HOME/.pngout"`. The files are named
like `<file-size>_<SHA1sum>.png`.

So when some image is optimized for the first time, the result is saved in
`$CACHEDIR`. On next requests for the same image optimization, the optimized
file is returned from the cache.

You need to install this script to `/usr/local/bin/pngout`.
