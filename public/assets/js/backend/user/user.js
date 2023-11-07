define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'user/user/index',
                    add_url: 'user/user/add',
                    edit_url: 'user/user/edit',
                    del_url: 'user/user/del',
                    multi_url: 'user/user/multi',
                    table: 'user',
                    audit_url: 'user/user/audit',
                    detail_url: 'user/user/show'
                }
            });

            var table = $("#table");
            var columns = [
                [
                    { checkbox: true },
                    { field: 'id', title: __('Id'), sortable: true },
                    { field: 'group.name', title: __('Group') },
                    { field: 'username', title: __('Username'), operate: 'LIKE' },
                    { field: 'nickname', title: __('Nickname'), operate: 'LIKE' },
                    { field: 'email', title: __('Email'), operate: 'LIKE' },
                    { field: 'mobile', title: __('Mobile'), operate: 'LIKE' },
                    { field: 'avatar', title: __('Avatar'), events: Table.api.events.image, formatter: Table.api.formatter.image, operate: false },
                    { field: 'level', title: __('Level'), operate: 'BETWEEN', sortable: true },
                    { field: 'gender', title: __('Gender'), visible: false, searchList: { 1: __('Male'), 0: __('Female') } },
                    { field: 'score', title: __('Score'), operate: 'BETWEEN', sortable: true },
                    { field: 'successions', title: __('Successions'), visible: false, operate: 'BETWEEN', sortable: true },
                    { field: 'maxsuccessions', title: __('Maxsuccessions'), visible: false, operate: 'BETWEEN', sortable: true },
                    { field: 'logintime', title: __('Logintime'), formatter: Table.api.formatter.datetime, operate: 'RANGE', addclass: 'datetimerange', sortable: true },
                    { field: 'loginip', title: __('Loginip'), formatter: Table.api.formatter.search },
                    { field: 'jointime', title: __('Jointime'), formatter: Table.api.formatter.datetime, operate: 'RANGE', addclass: 'datetimerange', sortable: true },
                    { field: 'joinip', title: __('Joinip'), formatter: Table.api.formatter.search },
                    { field: 'status', title: __('Status'), formatter: Table.api.formatter.status, searchList: { normal: __('Normal'), hidden: __('Hidden') } },
                    {
                        field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate, formatter: Table.api.formatter.operate,
                        buttons: [
                            {
                                name: 'detail',
                                title: '用户信息',
                                classname: 'btn btn-xs btn-info btn-dialog',
                                icon: 'fa fa-list-alt',
                                url: function (row) {
                                    return $.fn.bootstrapTable.defaults.extend.detail_url + "?id=" + row.id;
                                }
                            },
                            {
                                name: 'audit',
                                title: '审查用户',
                                classname: 'btn btn-xs btn-info btn-dialog',
                                icon: 'fa fa-file-text-o',
                                url: function (row) {
                                    return $.fn.bootstrapTable.defaults.extend.audit_url + "?id=" + row.id;
                                }
                            }
                        ]
                    }
                ]
            ];
            $.ajax({
                url: 'ajax/getMap?mapType=organizationJob',
                type: 'GET',
                dataType: 'json',
                success: function (response) {
                    var type = response.data;
                    columns = [
                        { checkbox: true },
                        { field: 'id', title: __('Id'), sortable: true },
                        { field: 'group.name', title: __('Group') },
                        { field: 'username', title: __('Username'), operate: 'LIKE' },
                        { field: 'nickname', title: __('Nickname'), operate: 'LIKE' },
                        { field: 'email', title: __('Email'), operate: 'LIKE' },
                        { field: 'mobile', title: __('Mobile'), operate: 'LIKE' },
                        { field: 'avatar', title: __('Avatar'), events: Table.api.events.image, formatter: Table.api.formatter.image, operate: false },
                        // { field: 'level', title: __('Level'), operate: 'BETWEEN', sortable: true },
                        { field: 'job', title: __('job'), searchList: type, formatter: Table.api.formatter.normal },
                        { field: 'createtime', title: __('Createtime'), operate: 'RANGE', addclass: 'datetimerange', autocomplete: false, formatter: Table.api.formatter.datetime },
                        { field: 'gender', title: __('Gender'), visible: false, searchList: { 1: __('Male'), 0: __('Female') } },
                        { field: 'score', title: __('Score'), operate: 'BETWEEN', sortable: true },
                        { field: 'successions', title: __('Successions'), visible: false, operate: 'BETWEEN', sortable: true },
                        { field: 'maxsuccessions', title: __('Maxsuccessions'), visible: false, operate: 'BETWEEN', sortable: true },
                        { field: 'logintime', title: __('Logintime'), formatter: Table.api.formatter.datetime, operate: 'RANGE', addclass: 'datetimerange', sortable: true },
                        { field: 'loginip', title: __('Loginip'), formatter: Table.api.formatter.search },
                        { field: 'jointime', title: __('Jointime'), formatter: Table.api.formatter.datetime, operate: 'RANGE', addclass: 'datetimerange', sortable: true },
                        { field: 'joinip', title: __('Joinip'), formatter: Table.api.formatter.search },
                        { field: 'status', title: __('Status'), formatter: Table.api.formatter.status, searchList: { normal: __('Normal'), hidden: __('Hidden') } },
                        {
                            field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate, formatter: Table.api.formatter.operate, buttons: [
                                {
                                    name: 'detail',
                                    title: '用户信息',
                                    classname: 'btn btn-xs btn-info btn-dialog',
                                    icon: 'fa fa-list-alt',
                                    url: function (row) {
                                        return $.fn.bootstrapTable.defaults.extend.detail_url + "?id=" + row.id;
                                    }
                                },
                                {
                                    name: 'audit',
                                    title: '审查用户',
                                    classname: 'btn btn-xs btn-info btn-dialog',
                                    icon: 'fa fa-file-text-o',
                                    url: function (row) {
                                        return $.fn.bootstrapTable.defaults.extend.audit_url + "?id=" + row.id;
                                    }
                                }
                            ]
                        }
                    ];

                    // 初始化表格
                    table.bootstrapTable({
                        url: $.fn.bootstrapTable.defaults.extend.index_url,
                        pk: 'id',
                        sortName: 'user.id',
                        columns: columns
                    });

                    // 为表格绑定事件
                    Table.api.bindevent(table);
                },
                error: function (xhr, textStatus, errorThrown) {
                    table.bootstrapTable({
                        url: $.fn.bootstrapTable.defaults.extend.index_url,
                        pk: 'id',
                        sortName: 'id',
                        columns: columns
                    });
                    // 为表格绑定事件
                    Table.api.bindevent(table);
                }
            });
        },
        add: function () {
            Controller.api.bindevent();
        },
        edit: function () {
            Controller.api.bindevent();
        },
        audit: function () {
            Controller.api.bindevent();
        },
        detail: function () {
            Controller.api.bindevent();
        },
        api: {
            bindevent: function () {
                Form.api.bindevent($("form[role=form]"));
            }
        }
    };
    return Controller;
});