#!/bin/sh
BASE=`dirname $0`/..
echo "Making coding standard for dir: $BASE ";

cd $BASE
if [ $? -eq 0 ] ; then
    mkdir -p report
    FILE="report/codingStandard.html";

    echo -e "<html> <pre> \n" >$FILE;
    echo -n "Built on " >> $FILE;
    date >> $FILE;
    echo -e "\n" >> $FILE;
    phpcs --standard=Fajr . --report=full --ignore=report | tr '<>' '()' >> $FILE;
    echo -e "\n\n\n++++++++++++++++++++++++++++++++++++++++++++++++++\n\n\n" >> $FILE;
    phpcs --standard=Fajr . --report=summary --ignore=report | tr '<>' '()' >> $FILE;
    echo "</pre></html>" >> $FILE;
else
    echo "Problems entering directory $BASE, exitting";
fi;
