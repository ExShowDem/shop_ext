#!/bin/bash

# Doc creator
# V. 1.0.2
# (c) redWEB 2017

clear

#Set usefull vars
red='\033[0;31m'
orange='\033[0;33m'
normal='\033[0m'
green='\033[0;32m'
error="${orange}[ ${red}ERROR ${orange}] ${normal}- "
complete="${orange}[ ${green}SUCCESS ${orange}] ${normal}- " 

#Get possible options
docHost=''
docPort=''
docUser=''
docPass=''
docPath=''
docLocalPath='docs/site/'
needHelp='notEmpty'

# Run through options
while getopts r:u:p:s:d:h option
do
case "${option}"
  in
  r)    docHost=${OPTARG};;
  u)    docUser=${OPTARG};;
  p)    docPass=${OPTARG};;
  s)    docPort=${OPTARG};;
	d)	  docPath=${OPTARG};;
	h)    needHelp='';;
  esac
done

# Check the options
if [ -z "$needHelp" ] || [ -z "$docHost" ] || [ -z "$docUser" ] || [ -z "$docPass" ] || [ -z "$docPort" ] || [ -z "$docPath" ];
then
	echo "Welcome to ${orange}DOC ${normal}creator Version ${red}1.0.0${normal}"
	echo "following params are required:"
	echo "${red}-r${orange}    HOST       ${normal}> The host to connect to"
	echo "${red}-u${orange}    USERNAME   ${normal}> The user name for the host"
	echo "${red}-p${orange}    PASSWORD   ${normal}> The password for the host"
	echo "${red}-s${orange}    PORT       ${normal}> The port of secure FTP"
	echo "${red}-d${orange}    DIRECTORY  ${normal}> The complete path to the remote directory eg: /home/user/domains/subdomain/public_html/REPONAME"
	echo "-h   ${orange} HELP       ${normal}> Shows this message"
	echo "all the Params in red is required for this script to run properly"
	exit
fi
#####################################
#                                   #
# install all required dependencies #
#                                   #
#####################################
echo "===================="
echo "executing composer install \n"

op=$(composer install 2>&1)
if [ $? -eq 0 ]
then
	echo $complete "phpDoc and its dependencies has been installed\n\n"
else
	echo $error "Couldn't install phpDoc and its dependencies\n"
	echo $op "\n"
	exit
fi;

######################################
#                                    #
# Run phpdoc to create documentation #
#                                    #
######################################
echo "===================="
echo "executing phpdoc \n"

op=$(phpdoc 2>&1)
if [ $? -eq 0 ]
then
	echo $complete "phpdoc returned no error\n\n"
else
	echo $error "this is embarrasing, but phpdoc couldn't run \n"
	echo $op "\n"
	exit
fi;

#######################################
#                                     #
# Make sure the directory site exists #
#                                     #
#######################################
echo "=========================="
echo "making sure $docLocalPath exists\n"

if [ -d "$docLocalPath" ]
then
	echo $complete "$docLocalPath exists \n\n"
else
	echo $error "$docLocalPath does not exists make sure configurations are set up properly!"
	exit
fi

##########################################
#                                        #
# Make sure the rsync.exp is executeable #
#                                        #
##########################################
echo "=========================="
echo "checking rsync.exp for executeable\n"

op=$( chmod +x rsync.exp )
if [ $? -eq 0 ]
then
	echo $complete "rsync.exp can be executed\n\n"
else 
	echo $error "couldn't chmod rsyng.exp exitting\n\n"
	exit
fi

#########################################################
#                                                       #
# Sync files from the generate doc to the remote server #
#                                                       #
#########################################################
echo "=========================="
echo "Beginning rsync sequence\n"

op=$( ./rsync.exp $docHost $docUser $docPass $docPort $docPath $docLocalPath)
ec=$?

if [ $ec -eq 0 ]
then
	echo $complete "Files are now synced to the remote location\n\n" 
elif [ $ec -eq 98 ] 
then
	echo $error "Directory $docPath doesn't exists please make sure your path is on the remote server"
echo $op
else
	echo $error "Could not sync files please check the connection info.\n"
echo $op
	exit
fi