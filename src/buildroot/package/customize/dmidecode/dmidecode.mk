#############################################################
#
# dmidecode
#
#############################################################
DMIDECODE_VERSION:=2.11
DMIDECODE_SOURCE:=dmidecode-$(DMIDECODE_VERSION).tar.bz2
DMIDECODE_SITE:=http://download.savannah.gnu.org/releases/dmidecode/
DMIDECODE_CAT:=$(BZCAT)
DMIDECODE_DIR:=$(BUILD_DIR)/dmidecode-$(DMIDECODE_VERSION)
DMIDECODE_BINARY:=dmidecode
DMIDECODE_TARGET_BINARY:=usr/sbin/dmidecode
DMIDECODE_DEPENDENCIES:=e2fsprogs

$(DL_DIR)/$(DMIDECODE_SOURCE):
	 $(call DOWNLOAD,$(DMIDECODE_SITE),$(DMIDECODE_SOURCE))

dmidecode-source: $(DL_DIR)/$(DMIDECODE_SOURCE)

$(DMIDECODE_DIR)/.unpacked: $(DL_DIR)/$(DMIDECODE_SOURCE)
	$(DMIDECODE_CAT) $(DL_DIR)/$(DMIDECODE_SOURCE) | tar -C $(BUILD_DIR) $(TAR_OPTIONS) -
	toolchain/patch-kernel.sh $(DMIDECODE_DIR) package/customize/dmidecode \*.patch
	touch $@

$(DMIDECODE_DIR)/$(DMIDECODE_BINARY): $(DMIDECODE_DIR)/.unpacked
	$(MAKE) $(TARGET_CONFIGURE_OPTS) -C $(DMIDECODE_DIR) \
		CFLAGS="$(TARGET_CFLAGS)" \
		LDFLAGS="$(TARGET_LDFLAGS)"

$(TARGET_DIR)/$(DMIDECODE_TARGET_BINARY): $(DMIDECODE_DIR)/$(DMIDECODE_BINARY)
	rm -f $(TARGET_DIR)/$(DMIDECODE_TARGET_BINARY)
	$(INSTALL) -D -m 0755 $(DMIDECODE_DIR)/$(DMIDECODE_BINARY) $(TARGET_DIR)/$(DMIDECODE_TARGET_BINARY)
ifeq ($(BR2_HAVE_DOCUMENTATION),y)
	$(INSTALL) -D $(DMIDECODE_DIR)/man/dmidecode.8 $(TARGET_DIR)/usr/share/man/man8/dmidecod.8
endif
	$(STRIPCMD) $(STRIP_STRIP_ALL) $@

dmidecode: e2fsprogs $(TARGET_DIR)/$(DMIDECODE_TARGET_BINARY)

dmidecode-clean:
	-$(MAKE) -C $(DMIDECODE_DIR) clean
	rm -f $(TARGET_DIR)/$(DMIDECODE_TARGET_BINARY)

dmidecode-dirclean:
	rm -rf $(DMIDECODE_DIR)

#############################################################
#
# Toplevel Makefile options
#
#############################################################
ifeq ($(BR2_PACKAGE_DMIDECODE),y)
TARGETS+=dmidecode
endif
