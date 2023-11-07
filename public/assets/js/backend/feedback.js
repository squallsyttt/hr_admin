define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'feedback/index' + location.search,
                    add_url: 'feedback/add',
                    edit_url: 'feedback/edit',
                    del_url: 'feedback/del',
                    multi_url: 'feedback/multi',
                    import_url: 'feedback/import',
                    table: 'feedback',
                    reply_url:'feedback/reply'
                }
            });

            var table = $("#table");

            var columns = [
                [
                    { checkbox: true },
                    { field: 'id', title: __('Id') },
                    { field: 'user.nickname', title: __('User_id') },
                    { field: 'type', title: __('Type') },
                    { field: 'content', title: __('Content'), operate: 'LIKE' },
                    { field: 'image', title: __('Image'), operate: false, events: Table.api.events.image, formatter: Table.api.formatter.image },
                    { field: 'mobile', title: __('Mobile'), operate: 'LIKE' },
                    { field: 'createtime', title: __('Createtime'), operate: 'RANGE', addclass: 'datetimerange', autocomplete: false, formatter: Table.api.formatter.datetime },
                    { field: 'updatetime', title: __('Updatetime'), operate: 'RANGE', addclass: 'datetimerange', autocomplete: false, formatter: Table.api.formatter.datetime },
                    { field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate, formatter: Table.api.formatter.operate }
                ]
            ];

            $.ajax({
                url: 'ajax/getMap?mapType=feedbackType',
                type: 'GET',
                dataType: 'json',
                success: function (response) {
                    var type = response.data;
                    columns = [
                        [
                            { checkbox: true },
                            { field: 'id', title: __('Id') },
                            { field: 'user.nickname', title: __('User_id') },
                            { field: 'type', title: __('Type'), searchList: type, formatter: Table.api.formatter.normal },
                            { field: 'content', title: __('Content'), operate: 'LIKE' },
                            { field: 'image', title: __('Image'), operate: false, events: Table.api.events.image, formatter: Table.api.formatter.image },
                            { field: 'mobile', title: __('Mobile'), operate: 'LIKE' },
                            { field: 'createtime', title: __('Createtime'), operate: 'RANGE', addclass: 'datetimerange', autocomplete: false, formatter: Table.api.formatter.datetime },
                            { field: 'updatetime', title: __('Updatetime'), operate: 'RANGE', addclass: 'datetimerange', autocomplete: false, formatter: Table.api.formatter.datetime },
                            {
                                field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate, formatter: Table.api.formatter.operate, buttons: [
                                    {
                                        name: 'reply',
                                        title: '回复',
                                        classname: 'btn btn-xs btn-info btn-dialog btn-rangeReply',
                                        icon: 'fa fa-comments-o',
                                        url: function (row) {
                                            return $.fn.bootstrapTable.defaults.extend.reply_url + "?id=" + row.id;
                                        }
                                    }
                                ]
                            }
                        ]
                    ];
                    // 初始化表格
                    table.bootstrapTable({
                        url: $.fn.bootstrapTable.defaults.extend.index_url,
                        pk: 'id',
                        sortName: 'id',
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
        reply: function () {
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
