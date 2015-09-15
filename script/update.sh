#!/bin/bash

SELF=`readlink -f $0`
ROOT=`dirname $(dirname $SELF)`

cd $ROOT

cd public
php reader.php