#/bin/sh!

#---------------------------------------------------------------------
#                         Tollerus
#                Conlang Dictionary System
#      < https://github.com/petermarkley/tollerus >
# 
# Copyright 2023 by Peter Markley <peter@petermarkley.com>.
# Distributed under the terms of the Lesser GNU General Public License.
# 
# This file is part of Tollerus.
# 
# Tollerus is free software: you can redistribute it and/or modify it
# under the terms of the Lesser GNU General Public License as
# published by the Free Software Foundation, either version 2.1 of the
# License, or (at your option) any later version.
# 
# Tollerus is distributed in the hope that it will be useful, but
# WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# Lesser GNU General Public License for more details.
# 
# You should have received a copy of the Lesser GNU General Public
# License along with Tollerus.  If not, see
# < http://www.gnu.org/licenses/ >.
# 
#----------------------------------------------------------------------

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
