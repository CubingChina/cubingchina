dir=`dirname "$0"`
wca_home='https://www.worldcubeassociation.org'
cd $dir
db_config="`dirname \`dirname \\\`pwd\\\`\``/config/wcaDb"
db_num=`expr \( \`cat $db_config\` + 1 \) % 2`
mysql_user='cubingchina'
mysql_pass=''
mysql_db="wca_$db_num"
echo "get export data from wca"
wget $wca_home/results/misc/export.html
zipname=`grep -o '\(WCA_export[0-9]\+_[0-9]\{8\}\.sql\.zip\)' export.html | tail -1`

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
if [ "$last_version" -ge "$version" ] && [ "$last_date" -ge "$date" ]
then
	rm -f export.html*
	exit
fi
>last
echo $version >> last
echo $date >> last

lftp -c "pget -n 20 '$wca_home/results/misc/$zipname' -o $zipname"
echo "unzip the export data"
unzip -o $zipname WCA_export.sql
echo "replace charset to utf8_general_ci"
sed -ri 's/latin1/utf8/g' WCA_export.sql
sed -ri 's/utf8_unicode_ci/utf8_general_ci/g' WCA_export.sql
echo "import data"
mysql --force --user=$mysql_user --password=$mysql_pass $mysql_db < WCA_export.sql
echo "import additional"
mysql --user=$mysql_user --password=$mysql_pass $mysql_db < additional.sql
rm -f export.html* WCA_export*
echo "build some data and clean cache"
echo -n $db_num > $db_config
../../yiic wca update
