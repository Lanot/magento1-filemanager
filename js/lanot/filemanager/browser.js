Mediabrowser.addMethods({
    selectFile: function (event) {
        var div = Event.findElement(event, 'DIV');
        div.toggleClassName('selected');
        if(this.canShowFileButtons()) {
            this.showFileButtons();
        } else {
            this.hideFileButtons();
        }
    },

    canShowFileButtons: function() {
        return (this.getSelectedItems().length > 0);
    },

    getSelectedItems: function() {
        return $$('div.filecnt.selected');
    },

    unselectAll: function() {
        $$('div.filecnt.selected').each(function(e) {
            e.removeClassName('selected');
        })
    },

    unselectExceptOne: function(div) {
        $$('div.filecnt.selected[id!="' + div.id + '"]').each(function(e) {
            e.removeClassName('selected');
        })
    },

    select: function(div){
        $(div).addClassName('selected');
    },

    clearClipboard: function() {
        this.clipBoard = {items:[], oldfolder: '', newfolder: '', cut: 0};
        this.oldNode = false;
    },

    copySelected: function(isCut) {
        var cut = 0;
        if (isCut) {
            cut = 1;
        }
        this.clipBoard = {items:[], oldfolder: this.currentNode.id, newfolder: '', cut: cut};
        this.oldNode = this.currentNode;
        var _this = this;
        var i = 0;
        this.getSelectedItems().each(function (e) {
            _this.clipBoard.items[i] = e.id.replace(/folder-/g,"");
            i++;
        });
        this.setPasteButtonTitle(this.pasteButtonMessage.replace(/%d/g,this.clipBoard.items.length));
        this.hideCopyButtons();
    },

    pasteSelected: function() {
        this.clipBoard.newfolder = this.currentNode.id;
        if (this.clipBoard.newfolder == this.clipBoard.oldfolder) {
            alert(this.copySameFolderMessage);
            return ;
        }

        var _items = this.clipBoard.items;
        var params = this.clipBoard;
        params.items = Object.toJSON(this.clipBoard.items);
        new Ajax.Request(this.copySelectedUrl, {
            parameters: params,
            onSuccess: function(transport) {
                try {
                    this.treeCleanNode(this.currentNode);
                    //remove items from old node
                    this.onAjaxSuccess(transport);
                    if (this.clipBoard.cut == 1) {
                        this.treeCleanNodeItems(_items);
                    }
                    this.clearClipboard();
                    this.showCopyButtons();
                    this.selectFolder(this.currentNode);
                } catch(e) {
                    this.hideCopyButtons();
                    alert(e.message);
                }
            }.bind(this)
        });
    },

    cancelSelected: function() {
        this.clearClipboard();
        this.showCopyButtons();
    },

    renameSelected: function() {
        if (this.getSelectedItems().length > 1) {
            alert(this.renameSelectedErrorMessage);
            return false;
        }

        var div = this.getSelectedItem();
        var divId = div.id.replace(/folder-/g,"");
        var oldName = div.getAttribute('name');
        var name = prompt(this.renameMessagePrompt, oldName);
        if (!name) {
            return false;
        }

        new Ajax.Request(this.renameSelectedUrl, {
            parameters: {name: name, file: divId},
            onSuccess: function(transport) {
                try {
                    this.treeCleanNode(this.currentNode);
                    this.onAjaxSuccess(transport);
                    this.selectFolder(this.currentNode);
                } catch(e) {
                    alert(e.message);
                }
            }.bind(this)
        });
    },

    deleteSelected: function() {
        if (!confirm(this.deleteSelectedConfirmationMessage)) {
            return false;
        }
        var ids = [];
        var i = 0;
        this.getSelectedItems().each(function (e) {
            ids[i] = e.id.replace(/folder-/g,"");
            i++;
        });
        new Ajax.Request(this.deleteFilesUrl, {
            parameters: {files: Object.toJSON(ids)},
            onSuccess: function(transport) {
                try {
                    this.treeCleanNode(this.currentNode);
                    this.onAjaxSuccess(transport);
                    this.selectFolder(this.currentNode);
                } catch(e) {
                    alert(e.message);
                }
            }.bind(this)
        });
    },

    getSelectedItem: function(event) {
        var div;
        if (event != undefined) {
            div = Event.findElement(event, 'DIV');
        } else {
            $$('div.selected').each(function (e) {
                div = $(e.id);
            });
        }
        if ($(div.id) == undefined) {
            return false;
        }
        return div;
    },

    showCopyButtons: function() {
        if (this.clipBoard && this.clipBoard.items.length) {
            return ;
        }
        this.showElement('button_copy_selected');
        this.showElement('button_cut_selected');
        this.hideElement('button_paste_selected');
        this.hideElement('button_cancel_selected');
    },

    hideCopyButtons: function() {
        if (!this.clipBoard || !this.clipBoard.items.length) {
            return ;
        }
        this.hideElement('button_copy_selected');
        this.hideElement('button_cut_selected');
        this.showElement('button_paste_selected');
        this.showElement('button_cancel_selected');
    },

    showFileButtons: function () {
        this.showElement('button_delete_selected');
        this.showElement('button_rename_selected');

        if (!this.clipBoard || (!this.clipBoard.items.length)) {
            this.showElement('button_copy_selected');
            this.showElement('button_cut_selected');
        }
    },

    hideFileButtons: function () {
        this.hideElement('button_delete_selected');
        this.hideElement('button_rename_selected');

        this.hideElement('button_copy_selected');
        this.hideElement('button_cut_selected');
    },

    showControls: function() {
        this.showBlock('contents-uploader');
        this.showBlock('button_copy_selected');
        this.showBlock('button_cut_selected');
        this.showBlock('button_paste_selected');
        this.showBlock('button_cancel_selected');
        this.showBlock('button_new_folder');
        this.showBlock('button_cancel_selected');
        this.showBlock('button_rename_selected');
        this.showBlock('button_delete_selected');
    },

    hideControls: function() {
        this.hideBlock('contents-uploader');
        this.hideBlock('button_copy_selected');
        this.hideBlock('button_cut_selected');
        this.hideBlock('button_paste_selected');
        this.hideBlock('button_cancel_selected');
        this.hideBlock('button_new_folder');
        this.hideBlock('button_cancel_selected');
        this.hideBlock('button_rename_selected');
        this.hideBlock('button_delete_selected');
    },

    hideBlock: function(id) {
        if ($(id) != undefined) {
            $(id).addClassName('no-display-block');
            //$(id).hide();
        }
    },

    showBlock: function(id) {
        if ($(id) != undefined) {
            $(id).removeClassName('no-display-block');
            //$(id).show();
        }
    },

    insert: function(event) {
        var div = this.getSelectedItem(event);
        this.unselectExceptOne(div);
        if ($(div).hasClassName('folder')) {
            var folderId = div.id.replace(/folder-/g,"");
            if (folderId) {
                this.selectFolderById(folderId);
            }
        } else {
            this.downloadFile(div.title);
        }
    },

    newFolder: function() {
        var folderName = prompt(this.newFolderPrompt);
        if (!folderName) {
            return false;
        }
        new Ajax.Request(this.newFolderUrl, {
            parameters: {name: folderName},
            onSuccess: function(transport) {
                try {
                    this.treeCleanNode(this.currentNode);
                    this.onAjaxSuccess(transport);
                    this.selectFolder(this.currentNode);
                } catch (e) {
                    alert(e.message);
                }
            }.bind(this)
        })
    },

    addFolder: function(_node){
        if (!_node || !_node.id || !_node.short_name) {
            return false;
        }
        var existedNode = this.tree.getNodeById(_node.id);
        if (existedNode && existedNode.id) {
            return false;
        }
        var newNode = new Ext.tree.AsyncTreeNode({
            text: _node.short_name,
            id:_node.id,
            draggable:false,
            expanded: false
        });
        var child = this.currentNode.appendChild(newNode);
    },

    downloadFile: function(url) {
        if (!url) {
            return false;
        }
        var downloadLinkID = 'lanot_filemanager_downloader_link';
        var link = document.getElementById(downloadLinkID);
        if (!link) {
            link = document.createElement('a');
            link.id = downloadLinkID;
            link.target = '_blank';
            link.style.display = 'none';
            document.body.appendChild(link);
        }
        link.href = url;
        link.click();
    },

    expandCurrentFolder: function() {
        this.currentNode.loaded = true;
        this.currentNode.expand();
    },

    setPasteButtonTitle: function(title) {
        $$('#button_paste_selected span')[0].innerHTML= title;
    },

    treeCleanNode: function(node) {
        if (!node || !node.hasChildNodes()) {
            return;
        }
        while (node.hasChildNodes()) {
            if (!node.item(0)) {
                break;
            }
            node.removeChild(node.item(0));
        }
    },

    treeCleanNodeItems: function(items) {
        if (!items || (items.length == 0) || !this.oldNode) {
            return;
        }
        for(var i =0; i < items.length; i++) {
            var node = this.tree.getNodeById(items[i]);
            if (node && node.id) {
                this.oldNode.removeChild(node);
            }
        }
    }
});
