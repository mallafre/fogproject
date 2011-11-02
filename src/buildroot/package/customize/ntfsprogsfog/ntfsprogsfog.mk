#############################################################
#
# ntfsprogs
#
#############################################################
NTFSPROGSFOG_VERSION:=2.0.0
NTFSPROGSFOG_SOURCE:=ntfsprogs-$(NTFSPROGSFOG_VERSION).tar.gz
NTFSPROGSFOG_SITE:=http://$(BR2_SOURCEFORGE_MIRROR).dl.sourceforge.net/sourceforge/linux-ntfs/
NTFSPROGSFOG_CONF_OPT:=--disable-gnome-vfs --program-prefix="" --disable-crypto
NTFSPROGSFOG_INSTALL_STAGING:=yes

NTFSPROGSFOG_BIN:=ntfscat ntfscluster ntfscmp ntfsfix ntfsinfo ntfsls
NTFSPROGSFOG_SBIN:=ntfsclone ntfscp ntfslabel ntfsresize ntfsundelete mkntfs

ifeq ($(BR2_PACKAGE_LIBFUSE),y)
NTFSPROGSFOG_DEPENDENCIES += libfuse
endif

define NTFSPROGSFOG_UNINSTALL_TARGET_CMDS
	rm -f $(TARGET_DIR)/usr/lib/libntfs.so*
	rm -f $(addprefix $(TARGET_DIR)/usr/bin/,$(NTFSPROGSFOG_BIN))
	rm -f $(addprefix $(TARGET_DIR)/usr/sbin/,$(NTFSPROGSFOG_SBIN))
	-unlink $(TARGET_DIR)/sbin/mkfs.ntfs
endef

$(eval $(call AUTOTARGETS,package/customize,ntfsprogsfog))
