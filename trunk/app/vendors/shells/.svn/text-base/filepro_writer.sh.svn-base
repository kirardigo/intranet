#!/bin/bash

# ##################
# IMPORTANT - Save this file with LF endings ONLY! Otherwise Linux cannot execute the file
# because it looks for /bin/bash[CR] as an interpreter.
# ##################

# This script is used by the filepro driver to invoke some filepro processing that will take data from a 
# mirrored filepro model and insert/update/delete actual filepro data.
#
# The script expects 5 arguments:
# 1. The name of the mirror model (case-sensitive).
# 2. The ID of the record in the mirror model to examine.
# 3. The number of times to try to wait for the processing to complete before giving up.
# 4. The initial wait interval to wait before checking to see if processing is complete.
# 5. The periodic wait interval between retries to wait between attempts to see if processing is complete.
#
# There is an optional 6th parameter (it doesn't matter what the value is) that, if present, triggers a high-speed
# mode of operation. This causes the process to not use any sort of wait periods and timeouts to try and wait for the filepro processing
# to end gracefully. Typically you only want to do this if you can guarantee that no one will be in the filepro tables that you're 
# writing to, because in the case of a locked record in filepro, you could be waiting indefinitely.

# Export environment variables needed by filepro
TERM="ansi"
PFCMARK="20"
export TERM PFCMARK

# Make sure we have the required number of arguments
if [ $# -lt 5 ] ; then
	echo "Usage: filepro_writer.sh model recordID retries initialWait periodicWait [highSpeedMode]"
	exit 1
fi

Debug=0
Tty="/dev/null"
HighSpeedMode=0
Model="$1"
ID="$2"
Retries="$3"
InitialWait="$4"
PeriodicWait="$5"

if [ `hostname` = "hcd-dev5.hcd.net" ] ; then
	Debug=1
	Tty="/dev/stdout"
fi

# Turn on high speed mode if we have a 6th argument (we don't care what the value is)
if [ $# -eq 6 ] ; then
	HighSpeedMode=1
fi

# If we're running high speed mode run the program in the foreground and wait for it to finish
if [ ${HighSpeedMode} -eq 1 ] ; then
	# Run the filepro program in the foreground
	/u/apps/appl/fp/dreport "${Model}" -FP iud_driver -SR ${ID} -U > ${Tty} 2>&1
else
	# Run the filepro program in the background
	/u/apps/appl/fp/dreport "${Model}" -FP iud_driver -SR ${ID} -U > ${Tty} 2>&1 &
	
	# Grab the process ID of the background job
	BackgroundProcessID=$!
	Attempts="0"
	
	# Sleep for an initial period before checking to see if the process finished in the background
	usleep ${InitialWait}
	
	# Now attempt to wait a set number of times until we either see the process go away (i.e. finish) or we
	# hit a retry limit.
	while [ -n "`ps -fp ${BackgroundProcessID} | tail -n '+2'`" -a ${Attempts} -lt ${Retries} ]
	do
		usleep ${PeriodicWait}
		Attempts=`expr ${Attempts} + 1`
	done

	# Hopefully by now the process finished, but if it's still running and we've hit our limit on the number
	# of retries to wait for the process to exit, we forceably kill the process.
	kill -9 ${BackgroundProcessID} > ${Tty} 2>&1
fi