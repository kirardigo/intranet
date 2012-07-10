#!/bin/bash

# ##################
# IMPORTANT - Save this file with LF endings ONLY! Otherwise Linux cannot execute the file
# because it looks for /bin/bash[CR] as an interpreter.
# ##################

# This script will execute a cake shell. The reason we need
# this script instead of just running a shell directly is because
# we're trying to run the shells from cron. When running from cron,
# the terminal is not set, which causes the shell to die. So, to run
# any shell, just call this script with the name of the shell as the argument.

TERM=linux
export TERM

if [ `hostname` = "hcd-dev5.hcd.net" ] ; then
	cd /var/www/html/dev-emrs.millers.com
else
	cd /var/www/html/emrs.millers.com
fi

./cake/console/cake "$@"