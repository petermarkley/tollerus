#/bin/sh!
SHDIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" >/dev/null 2>&1 && pwd )"
DIR="$( dirname "$( dirname "$( dirname "${SHDIR}" )" )" )"
US=`grep user     ${DIR}/conf/languages/mysql.json | cut -d'"' -f4`
PW=`grep password ${DIR}/conf/languages/mysql.json | cut -d'"' -f4`

for i in ${DIR}/private/languages/data/*.xml ; do
	j=`echo $i | rev | cut -d'/' -f1 | rev | cut -d'.' -f1`
	xsltproc -o ${DIR}/scripts/sql/languages/data/$j.sql ${DIR}/scripts/xslt/languages/to_sql.xsl $i
done

echo "drop database languages; create database languages; use languages;" | \
	cat - ${DIR}/scripts/sql/languages/schema.sql ${DIR}/scripts/sql/languages/data/* | \
	mysql -u ${US} -p${PW}
