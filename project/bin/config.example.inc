PROJET=giilda
PROJETURL=https://declaration.giilda.com/
TMP=/tmp/giilda
SYMFONYTASKOPTIONS="--application=generique --env=dev"
WORKINGDIR=/home/giilda/project/
XMLTESTDIR=$WORKINGDIR/web/test/xml/
COUCHHOST=localhost
COUCHPORT=5984
COUCHBASE=giilda
COUCHTEST=http://localhost:5984/giilda_test
REPLICATIONDOC=
ELASTHOST=localhost
ELASTPORT=9200
ELASTBASE=giilda
ELASTKIBANAINDEX=.kibana #ne pas changer
COUCHDISTANTHOST=distantnode
#PDFDIR=data/archive/
#SAMBA_IP=xxx.xxx.xxx.xxx
#SAMBA_SHAREDIR=aaaaa
#SAMBA_AUTH=/home/user/.samba/auth
#SAMBA_SAGESUBDIR=yyyy
#SAMBA_SAGEFILE=sage.txt
#SAGE_EMAILFILE=sage_report.log
#SAGE_EMAILS="test@example.org test2@example.org"
#VINSIEXPORT=VinsiClientsSage.txt
#SAMBA_FACTURELOCALDIR=zzzz
#SAMBA_SAGEVERIFY=1
#SAMBA_XLSOFT_IP=
#SAMBA_XLSOFT_SHARE=
#SAMBA_XLSOFT_DIR=
#DEV_EMAILS="test@example.org test2@example.org"
EXPORTDIR=MYDIR

METABASE=~/metabase/db/bi.sqlite

# Import export des DRM externes
DRMEXTERNEIMPORTDIR=MYEXTERNEDIR
GETDRMEXTERNECMD=()
PUTDRMEXTERNE=()

#Retour douane
CIEL_URL_RETOURXML=http://10.10.10.10/reception_douanes/SIRENINTERPO/
EMAILS_RETOURXML=""
URLDRMINTERNE=http://urlinterpro/drm/

mkdir -p $TMP > /dev/null
