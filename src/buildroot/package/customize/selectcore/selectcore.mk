#############################################################
#
# selectcore
#
#############################################################
SELECTCORE_VERSION:=7.4
SELECTCORE_SOURCE:=selectcore-$(SELECTCORE_VERSION).tar.gz
SELECTCORE_REAL_SOURCE:=coreutils-$(SELECTCORE_VERSION).tar.gz
#SELECTCORE_SITE:=ftp://alpha.gnu.org/gnu/selectcore/
SELECTCORE_SITE:=$(BR2_GNU_MIRROR)/coreutils
SELECTCORE_CAT:=$(ZCAT)
SELECTCORE_DIR:=$(BUILD_DIR)/selectcore-$(SELECTCORE_VERSION)
SELECTCORE_BINARY:=src/selectcore
SELECTCORE_TARGET_BINARY:=bin/selectcore
BIN_PROGS:=base64 shred

$(DL_DIR)/$(SELECTCORE_SOURCE):
	 $(call DOWNLOAD,$(SELECTCORE_SITE),$(SELECTCORE_REAL_SOURCE))
	 cp -f $(DL_DIR)/$(SELECTCORE_REAL_SOURCE) $(DL_DIR)/$(SELECTCORE_SOURCE)

selectcore-source: $(DL_DIR)/$(SELECTCORE_SOURCE)

$(SELECTCORE_DIR)/.unpacked: $(DL_DIR)/$(SELECTCORE_SOURCE)
	mkdir -p $(SELECTCORE_DIR)
	$(SELECTCORE_CAT) $(DL_DIR)/$(SELECTCORE_SOURCE) | tar --strip-components=1 -C $(SELECTCORE_DIR) $(TAR_OPTIONS) -
	toolchain/patch-kernel.sh $(SELECTCORE_DIR) package/customize/selectcore/ coreutils\*.patch
	$(CONFIG_UPDATE) $(SELECTCORE_DIR)/build-aux
	# ensure rename.m4 file is older than configure / aclocal.m4 so
	# auto* isn't rerun
	touch -d '1979-01-01' $(@D)/m4/rename.m4
	touch $@

$(SELECTCORE_DIR)/.configured: $(SELECTCORE_DIR)/.unpacked
	(cd $(SELECTCORE_DIR); rm -rf config.cache; \
		$(TARGET_CONFIGURE_OPTS) \
		$(TARGET_CONFIGURE_ARGS) \
		ac_cv_func_strtod=yes \
		ac_fsusage_space=yes \
		fu_cv_sys_stat_statfs2_bsize=yes \
		ac_cv_func_closedir_void=no \
		ac_cv_func_getloadavg=no \
		ac_cv_lib_util_getloadavg=no \
		ac_cv_lib_getloadavg_getloadavg=no \
		ac_cv_func_getgroups=yes \
		ac_cv_func_getgroups_works=yes \
		ac_cv_func_chown_works=yes \
		ac_cv_have_decl_euidaccess=no \
		ac_cv_func_euidaccess=no \
		ac_cv_have_decl_strnlen=yes \
		ac_cv_func_strnlen_working=yes \
		ac_cv_func_lstat_dereferences_slashed_symlink=yes \
		ac_cv_func_lstat_empty_string_bug=no \
		ac_cv_func_stat_empty_string_bug=no \
		gl_cv_func_rename_trailing_slash_bug=no \
		ac_cv_have_decl_nanosleep=yes \
		jm_cv_func_nanosleep_works=yes \
		gl_cv_func_working_utimes=yes \
		ac_cv_func_utime_null=yes \
		ac_cv_have_decl_strerror_r=yes \
		ac_cv_func_strerror_r_char_p=no \
		jm_cv_func_svid_putenv=yes \
		ac_cv_func_getcwd_null=yes \
		ac_cv_func_getdelim=yes \
		ac_cv_func_mkstemp=yes \
		utils_cv_func_mkstemp_limitations=no \
		utils_cv_func_mkdir_trailing_slash_bug=no \
		gl_cv_func_rename_dest_exists_bug=no \
		jm_cv_func_gettimeofday_clobber=no \
		am_cv_func_working_getline=yes \
		gl_cv_func_working_readdir=yes \
		jm_ac_cv_func_link_follows_symlink=no \
		utils_cv_localtime_cache=no \
		ac_cv_struct_st_mtim_nsec=no \
		gl_cv_func_tzset_clobber=no \
		gl_cv_func_getcwd_null=yes \
		gl_cv_func_getcwd_path_max=yes \
		ac_cv_func_fnmatch_gnu=yes \
		am_getline_needs_run_time_check=no \
		am_cv_func_working_getline=yes \
		gl_cv_func_mkdir_trailing_slash_bug=no \
		gl_cv_func_mkstemp_limitations=no \
		ac_cv_func_working_mktime=yes \
		jm_cv_func_working_re_compile_pattern=yes \
		ac_use_included_regex=no \
		gl_cv_c_restrict=no \
		./configure $(QUIET) \
		--target=$(GNU_TARGET_NAME) \
		--host=$(GNU_TARGET_NAME) \
		--build=$(GNU_HOST_NAME) \
		--prefix=/usr \
		--exec-prefix=/usr \
		--bindir=/usr/bin \
		--sbindir=/usr/sbin \
		--libdir=/lib \
		--libexecdir=/usr/lib \
		--sysconfdir=/etc \
		--datadir=/usr/share \
		--localstatedir=/var \
		--mandir=/usr/share/man \
		--infodir=/usr/share/info \
		$(DISABLE_NLS) \
		$(DISABLE_LARGEFILE) \
		--disable-rpath \
		--disable-dependency-tracking \
	)
	touch $@

$(SELECTCORE_DIR)/$(SELECTCORE_BINARY): $(SELECTCORE_DIR)/.configured
	$(MAKE) -C $(SELECTCORE_DIR)
	rm -f $(TARGET_DIR)/$(SELECTCORE_TARGET_BINARY)

$(TARGET_DIR)/$(SELECTCORE_TARGET_BINARY): $(SELECTCORE_DIR)/$(SELECTCORE_BINARY)
	$(MAKE) DESTDIR=$(TARGET_DIR) CC="$(TARGET_CC)" -C $(SELECTCORE_DIR) install
	# some things go in root rather than usr
	for f in $(BIN_PROGS); do \
		mv $(TARGET_DIR)/usr/bin/$$f $(TARGET_DIR)/bin/$$f; \
	done
	# link for archaic shells
	ln -fs test $(TARGET_DIR)/usr/bin/[
	# gnu thinks chroot is in bin, debian thinks it's in sbin
	mv $(TARGET_DIR)/usr/bin/chroot $(TARGET_DIR)/usr/sbin/chroot
	$(STRIPCMD) $(TARGET_DIR)/usr/sbin/chroot > /dev/null 2>&1
	rm -rf $(TARGET_DIR)/share/locale

# If both selectcore and busybox are selected, make certain selectcore
# wins the fight over who gets to have their utils actually installed.
ifeq ($(BR2_PACKAGE_BUSYBOX),y)
selectcore: busybox $(TARGET_DIR)/$(SELECTCORE_TARGET_BINARY)
else
selectcore: $(TARGET_DIR)/$(SELECTCORE_TARGET_BINARY)
endif

# If both selectcore and busybox are selected, the corresponding applets
# may need to be reinstated by the clean targets.
selectcore-clean:
	$(MAKE) DESTDIR=$(TARGET_DIR) CC=$(TARGET_CC) -C $(SELECTCORE_DIR) uninstall
	-$(MAKE) -C $(SELECTCORE_DIR) clean

selectcore-dirclean:
	rm -rf $(SELECTCORE_DIR)

#############################################################
#
# Toplevel Makefile options
#
#############################################################
ifeq ($(BR2_PACKAGE_SELECTCORE),y)
TARGETS+=selectcore
endif
