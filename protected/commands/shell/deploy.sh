dir=`dirname "$0"`
cd $dir
cd ../../../
git pull
cd protected
./yiic migrate
