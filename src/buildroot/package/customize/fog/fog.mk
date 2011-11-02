#############################################################
#
# fog
#
#############################################################
FOG_DIR:=$(BUILD_DIR)/fog_initrd_files
FOG_DEPENDENCIES = parted

$(FOG_DIR)/.unpacked: $(DL_DIR)/$(FOG_SOURCE)
	mkdir -p $(FOG_DIR)
	cp -r package/customize/fog/src/* $(FOG_DIR)
	touch $@

$(FOG_DIR)/fog_initrd_binaries: $(FOG_DIR)/.unpacked
	$(MAKE) $(TARGET_CONFIGURE_OPTS) -C $(FOG_DIR) \
		CXXFLAGS="$(TARGET_CXXFLAGS)" \
		LDFLAGS="$(TARGET_LDFLAGS)" 

$(TARGET_DIR)/bin/fogmbrfix: $(FOG_DIR)/fog_initrd_binaries
	rm -f $(TARGET_DIR)/bin/fogmbrfix
	$(INSTALL) -D -m 0755 $(FOG_DIR)/fogmbrfix $(TARGET_DIR)/bin/fogmbrfix
	$(STRIPCMD) $(STRIP_STRIP_ALL) $@

$(TARGET_DIR)/bin/fogpartinfo: $(FOG_DIR)/fog_initrd_binaries
	rm -f $(TARGET_DIR)/bin/fogpartinfo
	$(INSTALL) -D -m 0755 $(FOG_DIR)/fogpartinfo $(TARGET_DIR)/bin/fogpartinfo
	$(STRIPCMD) $(STRIP_STRIP_ALL) $@

fogscripts: 
	$(foreach script, \
	$(shell find package/customize/fog/scripts/ -type f | sed 's:package/customize/fog/scripts/:./:g'),  \
	$(INSTALL) -D -m 0755 package/customize/fog/scripts/$(script) $(TARGET_DIR)/$(script);)

fogpartimage: 
	$(foreach binfile, \
	$(shell find package/customize/fog/partimage/ -type f | sed 's:package/customize/fog/partimage/:./:g'),  \
	$(INSTALL) -D -m 0755 package/customize/fog/partimage/$(binfile) $(TARGET_DIR)/usr/sbin/$(binfile);)

fogpartclone: 
	$(foreach binfile, \
	$(shell find package/customize/fog/partclone/ -type f | sed 's:package/customize/fog/partclone/:./:g'),  \
	$(INSTALL) -D -m 0755 package/customize/fog/partclone/$(binfile) $(TARGET_DIR)/usr/sbin/$(binfile);)
	
inittabfix:
	sed -i 's/^tty1.*/tty1::respawn:\/sbin\/getty -i -n -l \/bin\/sh 38400 tty1/; s/^tty2/#tty2/' $(TARGET_DIR)/etc/inittab


fog: parted $(TARGET_DIR)/bin/fogmbrfix $(TARGET_DIR)/bin/fogpartinfo fogscripts inittabfix fogpartimage fogpartclone

fog-clean:
	-$(MAKE) -C $(FOG_DIR) clean
	rm -f $(TARGET_DIR)/bin/fogmbrfix
	rm -f $(TARGET_DIR)/bin/partinfo
	$(foreach script, \
	$(shell find package/customize/fog/scripts/ -type f | sed 's:package/customize/fog/scripts/:./:g'),  \
	rm -f $(script);)

fog-dirclean:
	rm -rf $(FOG_DIR)

#############################################################
#
# Toplevel Makefile options
#
#############################################################
ifeq ($(BR2_PACKAGE_FOG),y)
TARGETS+=fog
endif
