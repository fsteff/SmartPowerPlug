#!/bin/sh
relais=1
t=$1
shift
while [ -n "$1" ]
do
rel=`echo $1 | sed "s/t/$t/" `
if [ $rel ]
then 
  echo "$rel" OK
else
  echo "$rel" NOT OK
  relais=0
fi
shift
done
echo $relais
