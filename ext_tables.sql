#
# Table structure for table 'tx_rkwshop_domain_model_order'
#
CREATE TABLE tx_rkwshop_domain_model_order (

	uid int(11) NOT NULL auto_increment,
	pid int(11) DEFAULT '0' NOT NULL,

	email varchar(255) DEFAULT '' NOT NULL,
	frontend_user int(11) unsigned DEFAULT '0',
	order_item text NOT NULL,
	shipping_address int(11) DEFAULT '0' NOT NULL,
	remark text NOT NULL,

	tstamp int(11) unsigned DEFAULT '0' NOT NULL,
	crdate int(11) unsigned DEFAULT '0' NOT NULL,
	cruser_id int(11) unsigned DEFAULT '0' NOT NULL,
	deleted tinyint(4) unsigned DEFAULT '0' NOT NULL,
	hidden tinyint(4) unsigned DEFAULT '0' NOT NULL,
	status tinyint(4) unsigned DEFAULT '0' NOT NULL,

	PRIMARY KEY (uid),
	KEY parent (pid),

);

#
# Table structure for table 'tx_rkwshop_domain_model_orderitem'
#
CREATE TABLE tx_rkwshop_domain_model_orderitem (

	uid int(11) NOT NULL auto_increment,
	pid int(11) DEFAULT '0' NOT NULL,

	ext_order int(11) unsigned DEFAULT '0',
	product int(11) unsigned DEFAULT '0',
	amount int(11) unsigned DEFAULT '0',
	is_pre_order tinyint(4) unsigned DEFAULT '0' NOT NULL,

	tstamp int(11) unsigned DEFAULT '0' NOT NULL,
	crdate int(11) unsigned DEFAULT '0' NOT NULL,
	cruser_id int(11) unsigned DEFAULT '0' NOT NULL,
	deleted tinyint(4) unsigned DEFAULT '0' NOT NULL,
	status tinyint(4) unsigned DEFAULT '0' NOT NULL,

	PRIMARY KEY (uid),
	KEY parent (pid),

);


#
# Table structure for table 'tx_rkwshop_domain_model_product'
#
CREATE TABLE tx_rkwshop_domain_model_product (

	uid int(11) NOT NULL auto_increment,
	pid int(11) DEFAULT '0' NOT NULL,

	record_type varchar(255) DEFAULT '0' NOT NULL,

	title varchar(255) DEFAULT '' NOT NULL,
	subtitle varchar(255) DEFAULT '' NOT NULL,
	publishing_date int(11) unsigned DEFAULT '0' NOT NULL,
	author varchar(255) DEFAULT '' NOT NULL,
	page varchar(255) DEFAULT '' NOT NULL,
	image int(11) unsigned NOT NULL default '0',
	download int(11) unsigned NOT NULL default '0',
	product_bundle int(11) unsigned DEFAULT '0',
	allow_single_order tinyint(4) unsigned DEFAULT '0' NOT NULL,

	stock varchar(255) DEFAULT '' NOT NULL,
    ordered_external int(11) unsigned DEFAULT '0' NOT NULL,

	backend_user varchar(255) DEFAULT '' NOT NULL,
	admin_email varchar(255) DEFAULT '' NOT NULL,

	tstamp int(11) unsigned DEFAULT '0' NOT NULL,
	crdate int(11) unsigned DEFAULT '0' NOT NULL,
	cruser_id int(11) unsigned DEFAULT '0' NOT NULL,
	deleted tinyint(4) unsigned DEFAULT '0' NOT NULL,
	hidden tinyint(4) unsigned DEFAULT '0' NOT NULL,

	sys_language_uid int(11) DEFAULT '0' NOT NULL,
	l10n_parent int(11) DEFAULT '0' NOT NULL,
	l10n_diffsource mediumblob,

	PRIMARY KEY (uid),
	KEY parent (pid),
	KEY language (l10n_parent,sys_language_uid)

);


#
# Table structure for table 'tx_rkwshop_domain_model_stock'
#
CREATE TABLE tx_rkwshop_domain_model_stock (

	uid int(11) NOT NULL auto_increment,
	pid int(11) DEFAULT '0' NOT NULL,

	product int(11) DEFAULT '0' NOT NULL,
	amount int(11) unsigned DEFAULT '500' NOT NULL,
	delivery_start int(11) unsigned DEFAULT '0' NOT NULL,
	comment varchar(255) DEFAULT '' NOT NULL,

	tstamp int(11) unsigned DEFAULT '0' NOT NULL,
	crdate int(11) unsigned DEFAULT '0' NOT NULL,
	cruser_id int(11) unsigned DEFAULT '0' NOT NULL,
	deleted tinyint(4) unsigned DEFAULT '0' NOT NULL,
	hidden tinyint(4) unsigned DEFAULT '0' NOT NULL,

	PRIMARY KEY (uid),
	KEY parent (pid)
);
