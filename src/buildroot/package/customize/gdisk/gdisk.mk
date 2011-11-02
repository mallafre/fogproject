#############################################################
#
# gdisk
#
#############################################################
GDISK_VERSION:=0.8.1
GDISK_SOURCE:=gdisk-$(GDISK_VERSION).tgz
GDISK_SITE:=http://www.rodsbooks.com/gdisk/
GDISK_CAT:=$(ZCAT)
GDISK_DIR:=$(BUILD_DIR)/gdisk-$(GDISK_VERSION)
GDISK_BINARY:=gdisk
GDISK_BINARY1:=sgdisk
GDISK_TARGET_BINARY:=usr/bin/gdisk
GDISK_TARGET_BINARY1:=usr/bin/sgdisk
PIGZ_DEPENDENCIES:=popt

$(DL_DIR)/$(GDISK_SOURCE):
	 $(call DOWNLOAD,$(GDISK_SITE),$(GDISK_SOURCE))

gdisk-source: $(DL_DIR)/$(GDISK_SOURCE)

$(GDISK_DIR)/.unpacked: $(DL_DIR)/$(GDISK_SOURCE)
	$(GDISK_CAT) $(DL_DIR)/$(GDISK_SOURCE) | tar -C $(BUILD_DIR) $(TAR_OPTIONS) -
	toolchain/patch-kernel.sh $(GDISK_DIR) package/customize/gdisk \*.patch
	touch $@

$(GDISK_DIR)/$(GDISK_BINARY): $(GDISK_DIR)/.unpacked
	$(MAKE) $(TARGET_CONFIGURE_OPTS) -C $(GDISK_DIR) \
		CFLAGS="$(TARGET_CFLAGS)" \
		LDFLAGS="$(TARGET_LDFLAGS)"

$(GDISK_DIR)/$(GDISK_BINARY1): $(GDISK_DIR)/.unpacked
	$(MAKE) $(TARGET_CONFIGURE_OPTS) -C $(GDISK_DIR) \
		CFLAGS="$(TARGET_CFLAGS)" \
		LDFLAGS="$(TARGET_LDFLAGS)"

$(TARGET_DIR)/$(GDISK_TARGET_BINARY): $(GDISK_DIR)/$(GDISK_BINARY)
	rm -f $(TARGET_DIR)/$(GDISK_TARGET_BINARY)
	$(INSTALL) -D -m 0755 $(GDISK_DIR)/$(GDISK_BINARY) $(TARGET_DIR)/$(GDISK_TARGET_BINARY)
	$(STRIPCMD) $(STRIP_STRIP_ALL) $@

$(TARGET_DIR)/$(GDISK_TARGET_BINARY1): $(GDISK_DIR)/$(GDISK_BINARY1)
	rm -f $(TARGET_DIR)/$(GDISK_TARGET_BINARY1)
	$(INSTALL) -D -m 0755 $(GDISK_DIR)/$(GDISK_BINARY1) $(TARGET_DIR)/$(GDISK_TARGET_BINARY1)
	$(STRIPCMD) $(STRIP_STRIP_ALL) $@

gdisk: popt $(TARGET_DIR)/$(GDISK_TARGET_BINARY)
sgdisk: popt $(TARGET_DIR)/$(GDISK_TARGET_BINARY1)

gdisk-clean:
	-$(MAKE) -C $(GDISK_DIR) clean
	rm -f $(TARGET_DIR)/$(GDISK_TARGET_BINARY)
	rm -f $(TARGET_DIR)/$(GDISK_TARGET_BINARY1)

gdisk-dirclean:
	rm -rf $(GDISK_DIR)

#############################################################
#
# Toplevel Makefile options
#
#############################################################
ifeq ($(BR2_PACKAGE_GDISK),y)
TARGETS+=gdisk 
TARGETS+=sgdisk 
endif
