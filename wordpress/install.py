# cci WordPress Configuration Script
# 13 July 2005
# Nathan R. Yergler

import sys
import getopt

SHORT_ARGS = "d:u:p:h:"
LONG_ARGS = ('dname=', 'dbuser=', 'dbpass=', 'dbhost=', 'file=', 'prefix=', )
OPT_MAP = { '--dbname'  : 'dbname',
			'-d'		: 'dbname',
			'--dbuser'  : 'dbuser',
			'-u'		: 'dbuser',
			'--dbpass'  : 'dbpass',
			'-p'		: 'dbpass',
			'--dbhost'  : 'dbhost',
			'-h'		: 'dbhost',
			'--file'	: 'file', 
			'--prefix'  : 'prefix',
			}
REQUIRED = ('dbname', 'dbuser', 'dbpass')

def usage():
	"""Output usage statement."""
	
	print """
install.py
ccInternational WordPress configuration utility

usage:

install.py [parameters, ...]

required parameters:
 -d | --dbname <name>     database name
 -u | --dbuser <username> database username
 -p | --dbpass <password> database password
 
optional parameters:
 -h | --dbhost <hostname> database hostname; defaults to localhost
 --file <output_file>     configuration file to write; defaults to wp-config.php
 --prefix <prefix>        database table prefix
 """
 
def parse_args(argv):
	params = {'dbhost':'localhost', 'file':'wp-config.php', 'prefix':'' }
	optlist, args = getopt.getopt(argv, SHORT_ARGS, LONG_ARGS)
	
	for optpair in optlist:
		params[OPT_MAP[optpair[0]]] = optpair[1]
	
	
	return params
	
if __name__ == '__main__':
	if len(sys.argv) <= 1:
		usage()
		sys.exit()
		
	params = parse_args(sys.argv[1:])
	for r in REQUIRED:
		if r not in params.keys():
			print "Missing paramter."
			usage()
			sys.exit(1)
	
	# load the template file
	config = file('wp-config-cci.php').read()
	
	# substitute parameters
	for k in params.keys():
		config = config.replace('__%s__' % k, params[k])
		
	file(params['file'], 'w').write(config)
	
	print 'Wrote config to %s' % params['file']