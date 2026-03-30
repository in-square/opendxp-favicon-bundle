/**
 * InSquare OpenDXP Favicon bundle.
 */

opendxp.registerNS("opendxp.plugin.insquarefavicon");
opendxp.registerNS("opendxp.settings.favicon");

opendxp.settings.favicon = Class.create({
    initialize: function () {
        this.getTabPanel();
    },

    getTabPanel: function () {
        if (!this.panel) {
            this.panel = Ext.create("Ext.panel.Panel", {
                id: "opendxp_settings_favicon",
                title: t("favicon"),
                iconCls: "opendxp_icon_image",
                border: false,
                layout: "fit",
                closable: true
            });

            this.panel.on("destroy", function () {
                opendxp.globalmanager.remove("settings_favicon");
            }.bind(this));

            this.layout = Ext.create("Ext.form.Panel", {
                bodyStyle: "padding:20px 5px 20px 5px;",
                border: false,
                autoScroll: true,
                forceLayout: true,
                defaults: {
                    forceLayout: true
                },
                fieldDefaults: {
                    labelWidth: 250
                },
                items: [
                    {
                        xtype: "fieldset",
                        title: t("favicon"),
                        collapsible: true,
                        width: "100%",
                        autoHeight: true,
                        items: [
                            {
                                xtype: "container",
                                html: t("favicon_upload_description"),
                                style: "margin-bottom:10px;"
                            },
                            {
                                xtype: "container",
                                id: "opendxp_favicon_preview",
                                html: '<img src="' + Routing.generate("insquare_opendxp_favicon_display") + '" />'
                            },
                            {
                                xtype: "button",
                                text: t("upload"),
                                iconCls: "opendxp_icon_upload",
                                handler: function () {
                                    opendxp.helpers.uploadDialog(
                                        Routing.generate("insquare_opendxp_favicon_upload"),
                                        null,
                                        function () {
                                            var cont = Ext.getCmp("opendxp_favicon_preview");
                                            var date = new Date();
                                            cont.update('<img src="' + Routing.generate("insquare_opendxp_favicon_display", {"_dc": date.getTime()}) + '" />');
                                        }.bind(this)
                                    );
                                }.bind(this)
                            },
                            {
                                xtype: "button",
                                text: t("delete"),
                                iconCls: "opendxp_icon_delete",
                                handler: function () {
                                    Ext.Ajax.request({
                                        url: Routing.generate("insquare_opendxp_favicon_delete"),
                                        method: "DELETE",
                                        success: function () {
                                            var cont = Ext.getCmp("opendxp_favicon_preview");
                                            var date = new Date();
                                            cont.update('<img src="' + Routing.generate("insquare_opendxp_favicon_display", {"_dc": date.getTime()}) + '" />');
                                        }
                                    });
                                }.bind(this)
                            }
                        ]
                    }
                ]
            });

            this.panel.add(this.layout);

            var tabPanel = Ext.getCmp("opendxp_panel_tabs");
            tabPanel.add(this.panel);
            tabPanel.setActiveItem(this.panel);

            opendxp.layout.refresh();
        }

        return this.panel;
    },

    activate: function () {
        var tabPanel = Ext.getCmp("opendxp_panel_tabs");
        tabPanel.setActiveItem("opendxp_settings_favicon");
    }
});

opendxp.plugin.insquarefavicon = Class.create({
    initialize: function () {
        if (opendxp.events.preMenuBuild) {
            document.addEventListener(opendxp.events.preMenuBuild, this.preMenuBuild.bind(this));
        } else {
            document.addEventListener(opendxp.events.opendxpReady, this.opendxpReady.bind(this));
        }
    },

    preMenuBuild: function (e) {
        var perspectiveCfg = opendxp.globalmanager.get("perspective");

        if (!perspectiveCfg.inToolbar("settings")) {
            return;
        }

        var user = opendxp.globalmanager.get("user");
        if (!(user && (user.admin || user.isAllowed("favicon_settings")))) {
            return;
        }

        var menu = e.detail.menu;
        if (!menu.settings || !menu.settings.items) {
            return;
        }

        menu.settings.items.push({
            text: t("favicon"),
            iconCls: "opendxp_nav_icon_thumbnails",
            itemId: "opendxp_menu_settings_favicon",
            handler: this.openFaviconSettings.bind(this)
        });
    },

    opendxpReady: function () {
        var perspectiveCfg = opendxp.globalmanager.get("perspective");

        if (!perspectiveCfg.inToolbar("settings")) {
            return;
        }

        var user = opendxp.globalmanager.get("user");
        if (!(user && (user.admin || user.isAllowed("favicon_settings")))) {
            return;
        }

        var menu = Ext.getCmp("opendxp_menu_settings");
        if (!menu) {
            return;
        }

        menu.add({
            text: t("favicon"),
            iconCls: "opendxp_nav_icon_thumbnails",
            itemId: "opendxp_menu_settings_favicon",
            handler: this.openFaviconSettings.bind(this)
        });
    },

    openFaviconSettings: function () {
        try {
            opendxp.globalmanager.get("settings_favicon").activate();
        } catch (e) {
            opendxp.globalmanager.add("settings_favicon", new opendxp.settings.favicon());
        }
    }
});

new opendxp.plugin.insquarefavicon();
