dir=`dirname "$0"`
cd $dir
cd ../../../
git pull
cd protected
./yiic migrate
cd ../public/f
(npm run build >/dev/null 2>/dev/null &)
