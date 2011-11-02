//Duplication & modifications (c) STFC 2007

/*
 *  SSHTools - Java SSH2 API
 *
 *  Copyright (C) 2002 Lee David Painter.
 *
 *  This program is free software; you can redistribute it and/or
 *  modify it under the terms of the GNU Library General Public License
 *  as published by the Free Software Foundation; either version 2 of
 *  the License, or (at your option) any later version.
 *
 *  You may also distribute it and/or modify it under the terms of the
 *  Apache style J2SSH Software License. A copy of which should have
 *  been provided with the distribution.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  License document supplied with your distribution for more details.
 *
 */

package com.sshtools.common.ui;

import java.awt.Component;
import java.awt.event.ActionEvent;
import javax.swing.Action;
import com.sshtools.common.util.BrowserLauncher;

/**
 *
 *
 * @author $author$
 * @version $Revision: 1.1 $
 */
public class FAQAction
    extends StandardAction {
  private final static String ACTION_COMMAND_KEY_ABOUT = "faq-command";
  private final static String NAME_ABOUT = "FAQ";
  private final static String SMALL_ICON_ABOUT =
      "/com/sshtools/common/ui/faq.png";
  private final static String LARGE_ICON_ABOUT = "";
  private final static int MNEMONIC_KEY_ABOUT = 'Q';

  /**
   * Creates a new FAQAction object.
   *
   * @param parent
   * @param application
   */
  public FAQAction() {
    putValue(Action.NAME, NAME_ABOUT);
    //putValue(Action.SMALL_ICON, getIcon(SMALL_ICON_ABOUT));
    //putValue(LARGE_ICON, getIcon(LARGE_ICON_ABOUT));
    putValue(Action.SHORT_DESCRIPTION,
             "The FAQ");
    putValue(Action.LONG_DESCRIPTION,
             "Show the Frequently Asked Questions");
    putValue(Action.MNEMONIC_KEY, new Integer(MNEMONIC_KEY_ABOUT));
    putValue(Action.ACTION_COMMAND_KEY, ACTION_COMMAND_KEY_ABOUT);
    putValue(StandardAction.ON_MENUBAR, new Boolean(true));
    putValue(StandardAction.MENU_NAME, "Help");
    putValue(StandardAction.MENU_ITEM_GROUP, new Integer(90));
    putValue(StandardAction.MENU_ITEM_WEIGHT, new Integer(70));
    putValue(StandardAction.ON_TOOLBAR, new Boolean(false));
    putValue(StandardAction.TOOLBAR_GROUP, new Integer(90));
    putValue(StandardAction.TOOLBAR_WEIGHT, new Integer(5));
  }

  /**
   *
   *
   * @param evt
   */
  public void actionPerformed(ActionEvent evt) {
      try {
	  BrowserLauncher.openURL("http://www.grid-support.ac.uk/content/view/138/195/"); 
      } catch (java.io.IOException ioe) {
          ioe.printStackTrace();
      }
  }
}
