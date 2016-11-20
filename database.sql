--
-- Database for IPAdmin.
-- Oskar Nilsson
-- 2003-07-12
--

--
-- S E S S I O N S
--
CREATE TABLE sessions (
	sesskey varchar(32) NOT NULL,
	expiry timestamp NOT NULL,
	value text,
	CONSTRAINT sessions_pkey PRIMARY KEY (sesskey)
);

CREATE RULE "session_clean" AS ON
	INSERT TO sessions DO DELETE FROM sessions WHERE expiry < now();

GRANT SELECT, UPDATE, INSERT, DELETE ON sessions TO ipadmin;

--
-- G R A Y L I S T
-- temporarily blocked IP addresses (Not allowed to use this system)
--
CREATE TABLE graylist (
	ip inet UNIQUE NOT NULL,
	nroftries int DEFAULT 1,
	blocked_until timestamp NOT NULL DEFAULT now() + '5 minutes',
	CONSTRAINT graylist_pkey PRIMARY KEY(ip)
);

CREATE RULE cleangraylist AS ON INSERT TO graylist
	DO DELETE FROM graylist WHERE blocked_until <= now();

CREATE VIEW blockedip (ip) AS
	SELECT ip FROM graylist
	WHERE blocked_until > now() AND nroftries >= 5;

GRANT SELECT, UPDATE, INSERT, DELETE ON greylist TO ipadmin;

--
-- L A N G U A G E S
--
CREATE SEQUENCE language_seq;

CREATE TABLE languages (
   id integer UNIQUE NOT NULL DEFAULT nextval('language_seq'),
   name varchar(20) UNIQUE NOT NULL,
   postfix varchar(2) UNIQUE NOT NULL,
   CONSTRAINT "language_pkey" PRIMARY KEY("id")
);

INSERT INTO languages (name, postfix) VALUES ('English', 'en');

GRANT SELECT ON languages TO ipadmin;

--
-- A D M I N I S T R A T O R S
--
CREATE SEQUENCE administrators_seq;

CREATE TABLE administrators (
	id integer UNIQUE NOT NULL DEFAULT nextval('administrators_seq'),
	username varchar(40) UNIQUE NOT NULL,
	password varchar(40) NOT NULL,
	name varchar (100) NOT NULL,
	email varchar(100) NOT NULL,
	level smallint NOT NULL,
	blocked boolean NOT NULL DEFAULT 'f',
	language integer NOT NULL DEFAULT 1,
	FOREIGN KEY(language) REFERENCES languages(id),
	CONSTRAINT administrators_pkey PRIMARY KEY(id)
);

GRANT SELECT, UPDATE, INSERT, DELETE ON administrators TO ipadmin;

--
-- H O U S E S
--
CREATE SEQUENCE houses_seq;

CREATE TABLE houses (
	id integer UNIQUE NOT NULL DEFAULT nextval('houses_seq'),
	name varchar(40) NOT NULL,
	CONSTRAINT houses_pkey PRIMARY KEY(id)
);

GRANT SELECT, UPDATE, INSERT, DELETE ON houses TO ipadmin;

--
-- A P A R T M E N T S
--
CREATE TABLE apartments (
	number integer UNIQUE NOT NULL,
	address varchar(100) NOT NULL,
	house integer NOT NULL,
	FOREIGN KEY(house) REFERENCES houses(id),
	CONSTRAINT apartments_pkey PRIMARY KEY(number)
);

GRANT SELECT, UPDATE, INSERT, DELETE ON apartments TO ipadmin;

--
-- S U B N E T
--
CREATE SEQUENCE subnets_seq;

CREATE TABLE subnets (
	id integer UNIQUE NOT NULL DEFAULT nextval('subnets_seq'),
	addr cidr NOT NULL,
	netmask cidr,
	gateway inet,
	local_subnet boolean DEFAULT 'f',
	CONSTRAINT subnets_pkey PRIMARY KEY(id)
);

CREATE FUNCTION "test_new_subnet" (cidr)
RETURNS boolean AS '
	DECLARE
		result RECORD;
		saddr ALIAS FOR $1;
	BEGIN
		FOR result IN SELECT addr FROM subnets LOOP
			IF saddr <<= result THEN return ''false''; END IF;
		END LOOP;
		return ''true'';
	END;
' LANGUAGE 'plpgsql';

GRANT SELECT, UPDATE, INSERT, DELETE ON subnets TO ipadmin;

--
-- I P _ N U M B E R S
--
CREATE SEQUENCE ip_number_seq;

CREATE TABLE ip_numbers (
	id integer UNIQUE NOT NULL DEFAULT nextval('ip_number_seq'),
	ip inet UNIQUE NOT NULL,
	subnet integer NOT NULL,
	use_as_extra_number boolean NOT NULL DEFAULT 'f',
	FOREIGN KEY(subnet) REFERENCES subnets(id),
	CONSTRAINT ip_numbers_pkey PRIMARY KEY(id)
);

GRANT SELECT, UPDATE, INSERT, DELETE ON ip_numbers TO ipadmin;

--
-- I P _ O B J E C T
-- Relation between an IP-number and an object
--
CREATE SEQUENCE ip_object_seq;

CREATE TABLE ip_object (
	id integer UNIQUE NOT NULL DEFAULT nextval('ip_apartment_seq'),
	ip integer NOT NULL,
	domain_name varchar(20) NOT NULL,
	start_time timestamp NOT NULL,
	end_time timestamp,
	FOREIGN KEY(ip) REFERENCES ip_numbers(id),
	UNIQUE (ip, end_time),
	CONSTRAINT ip_apartment_pkey PRIMARY KEY(id)
);

GRANT SELECT, UPDATE, INSERT, DELETE ON ip_object TO ipadmin;

--
-- I P _ A P A R M E N T
-- Relation between an IP-number and an apartment
--
CREATE TABLE ip_apartment (
	apartment integer NOT NULL,
	extra_number boolean NOT NULL DEFAULT 'f',
	FOREIGN KEY(apartment) REFERENCES apartments(number)
) INHERITS (ip_object);

GRANT SELECT, UPDATE, INSERT, DELETE ON ip_apartment TO ipadmin;

--
-- U S E R S
-- Persons responsible for an apartment.
--
CREATE SEQUENCE users_seq;

CREATE TABLE users (
	id integer UNIQUE NOT NULL DEFAULT nextval('users_seq'),
	fname varchar(32),
	lname varchar(32),
	phone varchar(12),
	mobile varchar(12),
	CONSTRAINT users_pkey PRIMARY KEY(id)
);

GRANT SELECT, UPDATE, INSERT, DELETE ON users TO ipadmin;

--
-- E M A I L
--
CREATE SEQUENCE email_seq;

CREATE TABLE email (
	id integer UNIQUE NOT NULL DEFAULT nextval('email_seq'),
	responsible_user integer NOT NULL,
	alias varchar(32) NOT NULL,
	pop varchar(8) NOT NULL,
	passwd varchar(16) NOT NULL,
	fname varchar(32),
	lname varchar(32),
	start_time timestamp NOT NULL,
	end_time timestamp,
	FOREIGN KEY (responsible_user) REFERENCES users(id),
	CONSTRAINT email_pkey PRIMARY KEY(id)
);

GRANT SELECT, UPDATE, INSERT, DELETE ON email TO ipadmin;

--
-- U S E R S _ A P A R T M E N T
--
CREATE SEQUENCE user_apartment_seq;

CREATE TABLE user_apartment (
	id integer UNIQUE NOT NULL DEFAULT nextval('user_apartment_seq'),
	user_id integer NOT NULL,
	apartment integer NOT NULL,
	start_time timestamp NOT NULL,
	end_time timestamp,
	FOREIGN KEY (user_id) REFERENCES users (id),
	FOREIGN KEY (apartment) REFERENCES apartments (number),
	CONSTRAINT user_apartment_pkey PRIMARY KEY (id)
);

GRANT SELECT, UPDATE, INSERT, DELETE ON users_apartment TO ipadmin;

--
-- W E B A C C O U N T
--
CREATE SEQUENCE webaccount_seq;

CREATE TABLE webaccount (
	id integer UNIQUE NOT NULL DEFAULT nextval('webaccount_seq'),
	user_apartment_id integer NOT NULL,
	account_name varchar(16) NOT NULL,
	passwd varchar(16) NOT NULL,
	FOREIGN KEY (user_apartment_id) REFERENCES user_apartment(id),
	CONSTRAINT webaccount_pkey PRIMARY KEY (id)
);

GRANT SELECT, UPDATE, INSERT, DELETE ON webaccount TO ipadmin;

--
-- E Q U I P M E N T
--
CREATE SEQUENCE equipment_seq;

CREATE TABLE equipment (
	id integer UNIQUE NOT NULL DEFAULT nextval ('equipment_seq'),
	house integer,
	name varchar(32),
	model varchar(32),
	FOREIGN KEY (house) REFERENCES houses (id),
	CONSTRAINT equipment_pkey PRIMARY KEY (id)
);

GRANT SELECT, UPDATE, INSERT, DELETE ON equipment TO ipadmin;

--
-- I P _ E Q U I P M E N T
-- Relation between an IP-number and an equipment.
--
CREATE TABLE ip_equipment (
	equipment_id integer NOT NULL,
	FOREIGN KEY (equipment_id) REFERENCES equipment (id)
) INHERITS (ip_object);

GRANT SELECT, UPDATE, INSERT, DELETE ON ip_equipment TO ipadmin;

--
-- P O R T
--
CREATE SEQUENCE port_seq;

CREATE TABLE port (
	id integer UNIQUE NOT NULL DEFAULT nextval ('port_seq'),
	equ integer NOT NULL,
	apartment integer,
	name varchar(12),
	FOREIGN KEY (que) REFERENCES equipment (id),
	FOREIGN KEY (apartment) REFERENCES apartments (number),
	CONSTRAINT port_pkey PRIMARY KEY (id)
);

GRANT SELECT, UPDATE, INSERT, DELETE ON port TO ipadmin;

--
-- P A G E   L O G I N
--
CREATE TABLE page_login (
   langid integer UNIQUE NOT NULL,
   text01 varchar(20) NOT NULL,
   text02 varchar(20) NOT NULL,
   text03 varchar(20) NOT NULL,
   text04 varchar(150) NOT NULL,
   text05 varchar(150) NOT NULL,
   text06 varchar(150) NOT NULL,
	text07 varchar(40) NOT NULL,
   FOREIGN KEY (langid) REFERENCES languages(id)
);

INSERT INTO page_login VALUES ('1',
   'Username',
   'Password',
   'Login',
   'Wrong password or username', --04
   'Your account have been blocked, please contact the administrator',
   'Your IP number has been blocked due to to many failed login attempts. Please try again in a few minutes',
	'IP-nummer administration'
);

GRANT SELECT ON page_login TO ipadmin;

--
-- P A G E   M E N U
--
CREATE TABLE page_menu (
   langid integer UNIQUE NOT NULL,
   text01 varchar(20) NOT NULL,
	text02 varchar(20) NOT NULL,
	text03 varchar(20) NOT NULL,
	text04 varchar(20) NOT NULL,
	text05 varchar(20) NOT NULL,
	text06 varchar(20) NOT NULL,
	text07 varchar(20) NOT NULL,
	text08 varchar(20) NOT NULL,
	text09 varchar(20) NOT NULL,
FOREIGN KEY (langid) REFERENCES languages(id)
);

INSERT INTO page_menu VALUES ('1',
	'Logout',
	'IP',
	'Users',
	'Apartments',
	'Administrators',	--05
	'Houses',
	'Subnets',
	'Administration',
	'Administrators'
);

GRANT SELECT ON page_menu TO ipadmin;

--
-- P A G E   A D M I N   I P
--
CREATE TABLE page_admin_ip (
   langid integer UNIQUE NOT NULL,
   text01 varchar(60) NOT NULL,
	text02 varchar(20) NOT NULL,
	text03 varchar(60) NOT NULL,
	text04 varchar(20) NOT NULL,
	text05 varchar(20) NOT NULL,
	text06 varchar(20) NOT NULL,
	text07 varchar(20) NOT NULL,
	text08 varchar(20) NOT NULL,
	text09 varchar(20) NOT NULL,
	text10 varchar(20) NOT NULL,
	text11 varchar(100) NOT NULL,
	text12 varchar(100) NOT NULL,
	text13 varchar(100) NOT NULL,
	text14 varchar(20) NOT NULL,
   FOREIGN KEY (langid) REFERENCES languages(id)
);

INSERT INTO page_admin_ip VALUES ('1',
   'Administrate IP-Numbers',
	'Save Changes',
	'No addresses in database',
	'IP-Address',
	'Owner',		--05
	'Edit',
	'Add Subnet',
	'Address',
	'Mask',
	'Done',	--10
	'You must enter all data',
	'IP number is not valid',
	'Mask is not valid',
	'Cancel'
);

GRANT SELECT ON page_admin_ip TO ipadmin;

--
-- P A G E   A D M I N   A D M I N I S T R A T O R S
--
CREATE TABLE page_admin_administrators (
   langid integer UNIQUE NOT NULL,
   text01 varchar(100) NOT NULL,
	text02 varchar(40) NOT NULL,
   text03 varchar(40) NOT NULL,
   text04 varchar(20) NOT NULL,
   text05 varchar(20) NOT NULL,
   text06 varchar(20) NOT NULL,
   text07 varchar(20) NOT NULL,
	text08 varchar(40) NOT NULL,
	text09 varchar(40) NOT NULL,
	text10 varchar(40) NOT NULL,
	text11 varchar(20) NOT NULL,
	text12 varchar(100) NOT NULL,
	text13 varchar(100) NOT NULL,
	text14 varchar(20) NOT NULL,
	text15 varchar(40) NOT NULL,
	text16 varchar(100) NOT NULL,
   text17 varchar(40) NOT NULL,
	text18 varchar(100) NOT NULL,
	text19 varchar(100) NOT NULL,
	
   FOREIGN KEY (langid) REFERENCES languages(id)
);

INSERT INTO page_admin_administrators VALUES ('1',
   'You are not allowed to view this page',
	'Administrators',
   'Add Administrator',
   'Username',
   'Name',		--05
   'Blocked',
   'Edit',
   'Super user',
	'E-mail address',
	'Password',	--10
	'Done',
	'You must enter all user data',
	'Password does not match',
	'Cancel',
	'Edit Administrator',	--15
	'That user do not exist',
	'Generate new password',
	'Adding new user failed',
	'Updating user failed'
);

GRANT SELECT ON page_admin_administrators TO ipadmin;

--
-- P A G E   A D M I N   A P A R T M E N T S
--
CREATE TABLE page_admin_apartments (
  langid integer UNIQUE NOT NULL,
	text01 varchar(40) NOT NULL,
   text02 varchar(40) NOT NULL,
   text03 varchar(20) NOT NULL,
	text04 varchar(20) NOT NULL,
	text05 varchar(20) NOT NULL,
	text06 varchar(40) NOT NULL,
	text07 varchar(40) NOT NULL,
	text08 varchar(20) NOT NULL,
	text09 varchar(20) NOT NULL,
	text10 varchar(100) NOT NULL,
	text11 varchar(100) NOT NULL,

   FOREIGN KEY (langid) REFERENCES languages(id)
);

INSERT INTO page_admin_apartments VALUES ('1',
   'Apartments',
   'No apartments in database',
   'Apartment',
   'Address',
	'Edit',		--05
	'Add Apartment',
	'Apartment number',
	'Add',
	'Cancel',
	'Adding new apartment failed (number exists?)', --10
	'You must enter all data'
);

GRANT SELECT TO page_admin_apartments TO ipadmin;

--
-- P A G E   A D M I N   S U B N E T S
--
CREATE TABLE page_admin_subnets (
	langid integer UNIQUE NOT NULL,
	text01 varchar(60) NOT NULL,
   text02 varchar(40) NOT NULL,
   text03 varchar(20) NOT NULL,
	text04 varchar(40) NOT NULL,
	text05 varchar(20) NOT NULL,
	text06 varchar(20) NOT NULL,
	text07 varchar(20) NOT NULL,
	text08 varchar(20) NOT NULL,
	text09 varchar(20) NOT NULL,
	text10 varchar(20) NOT NULL,
	text11 varchar(20) NOT NULL,
	text12 varchar(20) NOT NULL,
	text13 varchar(60) NOT NULL,
	text14 varchar(20) NOT NULL,
	text15 varchar(20) NOT NULL,
	text16 varchar(20) NOT NULL,
	text17 varchar(40) NOT NULL,
	text18 varchar(40) NOT NULL,
   FOREIGN KEY (langid) REFERENCES languages(id)
);

INSERT INTO page_admin_subnets VALUES ('1',
	'You are not allowed to view this page',
	'Administrate Subnets',
	'Add Subnet',
	'No subnets in the database',
	'Network addresss',	--05
	'Netmask',
	'Gateway',
	'Local subnet',
	'Edit',
	'Local',	--10
	'Public',
	'Edit Subnet',
	'Are you sure you want to delete the subnet?',
	'Delete',
	'Done',	--15
	'Cancel',
	'You must enter all data',
	'The subnet already exists'
);

GRANT SELECT ON page_admin_subnets TO ipadmin;

--
-- P A G E   A D M I N   H O U S E S
--
CREATE TABLE page_admin_houses (
	langid integer UNIQUE NOT NULL,

	FOREIGN KEY (langid) REFERENCES languages(id)
);

INSERT INTO page_admin_houses VALUES ('1'.
	'You are not allowed to view this page',
	'Administrate Houses',
	'Add a new house',
	'No houses in database',
	'House',	--05
	'Edit',
	'Edit House',
	'Can not find the requested house',
	'House name',
	'Delete',	--10
	'Save',
	'Cancel'
);

GRANT SELECT ON page_admin_houses TO ipadmin;
