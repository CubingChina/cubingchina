dir=`dirname "$0"`
cd $dir
cd ../../../
git pull
cd protected
composer update
./yiic migrate
cd ../public/f
#it takes long time to build
(npm run build >/dev/null 2>/dev/null &)
