create user 'serviceapi'@'localhost' identified by 'serviceapi@2017';
create user 'serviceapi'@'192.168.208.21' identified by 'serviceapi@2017';
create user 'serviceapi'@'192.168.208.147' identified by 'serviceapi@2017';
grant select,delete,update,insert,create on service_api.* to 'serviceapi'@'192.168.208.21';
grant select,delete,update,insert,create on service_api.* to 'serviceapi'@'192.168.208.147';
grant select,delete,update,insert,create on service_api.* to 'serviceapi'@'localhost';
flush privileges;