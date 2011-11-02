#############################################################
#
# parted
#
#############################################################
PARTED_VERSION:=3.0
PARTED_SOURCE:=parted-$(PARTED_VERSION).tar.gz
PARTED_SITE:=http://ftp.gnu.org/gnu/parted/
PARTED_INSTALL_STAGING=YES
PARTED_LIBTOOL_PATCH=NO
PARTED_CONF_OPT= --disable-device-mapper  \
		 --without-readline
PARTED_DEPENDENCIES:=e2fsprogs

$(eval $(call AUTOTARGETS,package/customize,parted))
