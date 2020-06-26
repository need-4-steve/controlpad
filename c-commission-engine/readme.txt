////////////////
// readme.txt //
////////////////

////////////////////////////
// #1 -  setup new server //
////////////////////////////
apt-get update
apt-get install apache2
apt-get install php7.2
apt-get install php7.2-curl
apt-get install php7.2-pgsql
apt-get install libapache2-mod-php
apt-get install g++
apt-get install postgresql
#apt-get install mysql-server
apt-get install libpq-dev
#apt-get install libmysqlclient-dev
apt-get install libcrypto++-dev
apt-get install libssl-dev
apt-get install libcurl4-gnutls-dev
apt-get install libjsoncpp-dev
apt-get install libgtest-dev
apt-get install cmake

apt-get install apache2 php7.0 php7.0-curl php7.0-pgsql g++ libapache2-mod-php postgresql mysql-server libpq-dev libmysqlclient-dev libcrypto++-dev libssl-dev libcurl4-gnutls-dev libjsoncpp-dev libgtest-dev cmake
 
cd /usr/src/gtest
sudo cmake .
sudo make
sudo mv libg* /usr/lib

// .htaccess file needed on server for some reason with the following settings //
// Error 500 happens without this //
// SecFilterEngine Off
// SecFilterScanPOST Off


/////////////////////////////////
// #2 - Congifure the database //
/////////////////////////////////

// Login as root //
sudo -s

// Change postgresql password. Use your own custom password when prompted //
passwd postgres

// Login using new password //
login postgres

// Add accounts so we can access the database //
createuser root
createuser ubuntu

// Login to postgresql command line //
psql postgres

// Update rights with your desired password //
ALTER USER root WITH PASSWORD 'yourpassword';
ALTER USER ubuntu WITH PASSWORD 'yourpassword';

// If web and database are on different servers, 
// Then make a change to the /etc/postgresql/9.4/main/pg_hba.conf file
// The reload the servce: /etc/init.d/postgresql reload

// Go back to ubuntu account //
exit
exit

// Verify your settings.ini file has postgres as the default database //

// Finally setup the database and tables //
// by going to the c-commissions-engine directory of the repository //
// and compile
./make
./api init
OR
./make-gtest

// Your database schema should be setup now //
// Make sure to put the correct username and password
// for database login 

///////////////////////
// #3 - Setup apache //
///////////////////////
// A sample fastcgi.conf file is lcated in the fastcgi directory //
// Use this to configure fastcgi in ref to your desired url //
// I only put the api file and settings.ini file in the given directory
// That way if there is a problem with apache2 configuration
// Then all the source files aren't compromised
// This change should be added to the /etc/apache2/sites-available/ directory

// Enable the change 
cd /etc/apache2/sites-enabled
ln -s ../sites-available/fastcgi.conf fastcgi.conf
/etc/init.d/apache2 restart

// At this point your should be setup
// If you have any problems then let me know
// West Anderson

// Installing google tests for unit testing //
//
// Running unit tests //
//

// To compile unit testing enable testing in Compile.h
// and run ./make-gtest


///////////////
// Ruby Rice //
///////////////
// Run the following commands to prepare for ruby rice compilation //
apt-get install ruby-dev
apt-get install rubygems-integration
gem install rice
gem install pg

// Run the following to compile ruby rice commission engine
ruby extconf.rb
ruby fix-makefile
make