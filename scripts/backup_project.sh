#help
if [ $# -lt 2 ]
then
  echo "Usage:"
  echo " backup_project.sh [folder] [when]"
  exit
fi

MY_PATH=`dirname "$0"`
MY_PATH=`( cd "$MY_PATH" && pwd )`
sudo -u archive-gs php $MY_PATH/../src/NGS/backup_run.php -in $1 -when $2 -out_folder /mnt/SRV018/raw_data_archive/projects/ ${@:3}
