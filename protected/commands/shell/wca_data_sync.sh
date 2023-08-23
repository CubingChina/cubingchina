dir=`dirname "$0"`
wca_home='https://www.worldcubeassociation.org'

_log () {
  echo "[`date +'%Y-%m-%d %H:%M:%S'`] $1"
}

cd $dir
db_config="`dirname \`dirname \\\`pwd\\\`\``/config/wcaDb"
db_num=`expr \( \`cat $db_config\` + 1 \) % 2`
mysql_user='cubingchina'
mysql_pass=''
mysql_db="wca_$db_num"
_log "get export data from wca"
wget $wca_home/export/results.html || exit
zipname=`grep -oP 'WCA_export\d+_\w+\.sql\.zip' export.html | tail -1`
_log "zipname: $zipname"
if [ $zipname = '' ]
then
  exit
fi
#check version and date
version=`echo $zipname | grep -o '\(WCA_export[0-9]\+\)' | grep -o '[0-9]\+' | tail -1`
date=`echo $zipname | grep -o '\([0-9]\{8\}\)' | tail -1`
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

lftp -c "pget -n 20 '$wca_home/export/results/$zipname' -o $zipname"
_log "unzip the export data"
unzip -o $zipname WCA_export.sql
_log "replace charset to utf8_general_ci"
sed -ri 's/utf8mb4/utf8/g' WCA_export.sql
sed -ri 's/unicode_ci/general_ci/g' WCA_export.sql
_log "remove drop table, disable create table"
sed -ri 's/DROP TABLE .+;//g' WCA_export.sql
sed -ri 's/CREATE TABLE/CREATE TABLE IF NOT EXISTS/g' WCA_export.sql
_log "add columns for insert"
sed -ri 's/INSERT INTO `Results`/INSERT INTO `Results` (`competitionId`,`eventId`,`roundTypeId`,`pos`,`best`,`average`,`personName`,`personId`,`personCountryId`,`formatId`,`value1`,`value2`,`value3`,`value4`,`value5`,`regionalSingleRecord`,`regionalAverageRecord`)/g' WCA_export.sql
sed -ri 's/INSERT INTO `RanksSingle`/INSERT INTO `RanksSingle` (`personId`,`eventId`,`best`,`worldRank`,`continentRank`,`countryRank`)/g' WCA_export.sql
sed -ri 's/INSERT INTO `RanksAverage`/INSERT INTO `RanksAverage` (`personId`,`eventId`,`best`,`worldRank`,`continentRank`,`countryRank`)/g' WCA_export.sql
_log "check for database"
mysql --user=$mysql_user --password=$mysql_pass -e "CREATE DATABASE IF NOT EXISTS $mysql_db CHARSET utf8"
_log "import structure"
mysql --force --user=$mysql_user --password=$mysql_pass $mysql_db < wca_structure.sql
_log "import data"
mysql --force --user=$mysql_user --password=$mysql_pass $mysql_db < WCA_export.sql
_log "import additional"
mysql --user=$mysql_user --password=$mysql_pass $mysql_db < additional.sql
rm -f export.html* WCA_export*
_log "build some data and clean cache"
echo -n $db_num > $db_config
../../yiic wca update
