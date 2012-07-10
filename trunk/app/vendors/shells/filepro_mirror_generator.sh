#!/bin/bash

# ##################
# IMPORTANT - Save this file with LF endings ONLY! Otherwise Linux cannot execute the file
# because it looks for /bin/bash[CR] as an interpreter.
# ##################

# This script automates the creation of a mirror file in filepro by
# sending commands to the ddefine program (basically pressing buttons
# on the screens). This way, the mirror file is created through filepro
# and the map file ends up with the proper encoded (blank) password on the
# first line. Note that the square characters on the next to last line
# are actually the ESC character twice at the beginning and twice at the end.
# That's not a mistake - they need to be there.

TERM=xterm
export TERM

# Make sure we have the required number of arguments
if [ $# -lt 1 ] ; then
	echo "Usage: filepro_mirror_generator.sh model"
	exit 1
fi

MODEL=$1

/u/apps/appl/fp/ddefine <<EOF

${MODEL}
2/u/apps/appl/filepro/${MODEL}/${MODEL}.DAT
Y

xnnn
EOF
