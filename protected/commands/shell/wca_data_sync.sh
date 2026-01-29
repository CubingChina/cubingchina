#!/bin/bash
dir=`dirname "$0"`
wca_home='https://www.worldcubeassociation.org'

_log () {
  echo "[`date +'%Y-%m-%d %H:%M:%S'`] $1"
}

# check if it's syncing
process_num=`ps aux | grep wca_data_sync.sh | grep -v grep -c`
_log "process_num: $process_num"
if [ $process_num -gt 3 ]
then
  _log "syncing, exit"
  exit
fi

cd $dir
db_config="`dirname \`dirname \\\`pwd\\\`\``/config/wcaDb"
db_num=`expr \( \`cat $db_config\` + 1 \) % 2`
mysql_user='cubingchina'
mysql_pass=''
mysql_db="wca_v2_$db_num"
_log "get export data from wca"
wget $wca_home/export/results -O export.html || exit
ziplink=`grep -oP 'href="\K[^"]+WCA_export_v2_\d+_\w+\.sql\.zip' export.html | tail -1`
zipname=`grep -oP 'WCA_export_v2_\d+_\w+\.sql\.zip' export.html | tail -1`
_log "zipname: $zipname"
if [ "dummy"$zipname = 'dummy' ]
then
  rm export.html*
  exit
fi
#check version and date
version=`echo $zipname | grep -oP 'WCA_export_v2_\K[0-9]+' | tail -1`
date=`echo $zipname | grep -oP '[0-9]{8}' | tail -1`
_log "version: $version"
_log "date: $date"
if [ -f last ]
then
  last_version=`sed -n '1,1p' last`
  last_date=`sed -n '2,1p' last`
  if [ "$last_version" = "" ]
  then
    last_version=0
  fi
  if [ "$last_date" = "" ]
  then
    last_date=0
  fi
else
  last_version=0
  last_date=0
fi
_log "last_version: $last_version"
_log "version: $version"
_log "last_date: $last_date"
_log "date: $date"
if [ "$last_version" -ge "$version" ] && [ "$last_date" -ge "$date" ]
then
  rm -f export.html*
  exit
fi
>last
echo $version >> last
echo $date >> last


lftp -c "set ssl:verify-certificate no; pget -n 20 '$ziplink' -o $zipname"
# check file size
zipfilesize=$(stat -c%s $zipname)
if (( zipfilesize < 10000000 ))
then
  rm export.html*
  rm $zipname
  _log "file too small: $zipfilesize"
  exit
fi
_log "unzip the export data"
unzip -o $zipname WCA_export.sql
sed -ri '/enable the sandbox mode/d' WCA_export.sql
_log "check for database"
_log "mysql_db: $mysql_db"
mysql --user=$mysql_user --password=$mysql_pass -e "CREATE DATABASE IF NOT EXISTS $mysql_db CHARSET utf8" || exit
_log "import structure"
mysql --force --user=$mysql_user --password=$mysql_pass $mysql_db < wca_structure.sql || exit
_log "import data"
mysql --force --user=$mysql_user --password=$mysql_pass $mysql_db < WCA_export.sql || exit
_log "import additional"
mysql --user=$mysql_user --password=$mysql_pass $mysql_db < additional.sql || exit
rm -f export.html* WCA_export*
_log "build some data and clean cache"
echo -n $db_num > $db_config
../../yiic wca update
