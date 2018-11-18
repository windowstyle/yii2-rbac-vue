<?php

/* @var $generator wyrbac\crud\Generator */

$controllerName = $generator->getControllerID();

?>
<template>
    <div class="app-container">
        <!-- 搜索栏 -->
        <div class="filter-container">
<?php
$count = 0;
foreach ($generator->getColumnNames() as $attribute) {
    if (++$count < 6) {
        echo "            " . $generator->generateSearchField($attribute) . "\n\n";
    } else {
        echo "            <!-- " . $generator->generateSearchField($attribute) . " -->\n\n";
    }
}
?>
            <el-button class="filter-item" type="primary" icon="el-icon-search" @click="OnSearchFilter">{{ $t('table.search') }}</el-button>
        </div>
        <div class="filter-container">
            <el-button class="filter-item" type="success" icon="el-icon-circle-plus-outline" @click="OnShowCreateDialog">{{ $t('table.add') }}</el-button>

            <el-button class="filter-item" type="danger" icon="el-icon-delete" @click="OnSelectRowDelete()">{{ $t('table.batchdelete') }}</el-button>
        </div>

        <!-- table展示列表 -->
        <el-table v-loading="searchLoading" :data="searchList" fit highlight-current-row @sort-change="OnSearchSortChange" @selection-change="OnSelectRowChange" style="width: 100%;">
            <el-table-column type="selection" width="55"></el-table-column>

<?php
$count = 0;
foreach ($generator->getColumnNames() as $attribute) {
    if (++$count < 6) {
        echo "            " . $generator->generateListField($attribute) . "\n\n";
    } else {
        echo "            <!-- " . $generator->generateListField($attribute) . " -->\n\n";
    }
}
?>

            <el-table-column :label="$t('table.actions')" align="center" width="100px">
                <template slot-scope="props">
                    <el-button type="primary" circle size="mini" icon="el-icon-edit" @click="OnShowUpdateDialog(props.row)"></el-button>
                    <el-button type="danger" circle size="mini" icon="el-icon-delete" @click="OnSelectRowDelete(props.row.id)"></el-button>
                </template>
            </el-table-column>
        </el-table>

        <div class="pagination-container">
            <el-pagination v-show="searchTotal > 0" :current-page="searchModel.page" :page-sizes="[10,20,30, 50]" :page-size="searchModel.limit" :total="searchTotal"
               @size-change="OnPageSizeChange" @current-change="OnCurrentPageChange" background layout="total, sizes, prev, pager, next, jumper"/>
        </div>

        <!-- 编辑框 -->

        <el-dialog :title="formDialogType == 'create' ? $t('<?=$controllerName ?>.create<?=ucfirst($controllerName) ?>') : $t('<?=$controllerName ?>.update<?=ucfirst($controllerName) ?>')" :visible.sync="formDialogVisible">
            <el-form ref="formDialogModel" v-loading="formDialogLoading" :model="formDialogModel" :rules="formDialogRule" status-icon>
<?php
$count = 0;
foreach ($generator->getColumnNames() as $attribute) {
    if (++$count < 6) {
        echo "                " . $generator->generateDialogField($attribute) . "\n\n";
    } else {
        echo "                <!-- " . $generator->generateDialogField($attribute) . " -->\n\n";
    }
}
?>
            </el-form>
            <div slot="footer" class="dialog-footer">
                <el-button @click="formDialogVisible = false">{{$t('table.cancel')}}</el-button>
                <el-button type="primary" :loading="formDialogLoading" @click="OnSubmitFormDialog">{{$t('table.confirm')}}</el-button>
            </div>
        </el-dialog>
    </div>
</template>

<script>
    import request from '@/utils/request'

    export default {
        name: '<?=$controllerName ?>', //Todo: 修改名称
        data() {
            return {
                // 状态相关
                statusOptions: [
                    {'label': 'Yes', 'value': 1},
                    {'label': 'No', 'value': 2}
                ],
                statusMap: {1: 'Yes', 2: 'No'},

                // 搜索相关
                searchModel: { // Todo: 修改搜索项的model
<?php
$count = 0;
foreach ($generator->getColumnNames() as $name) {
    if (++$count < 6) {
        echo "                    " . $name . ":undefined,\n";
    } else {
        echo "                    //" . $name . ":undefined,\n";
    }
}
?>
                    page: 1,
                    limit: 10,
                    sort: '-id'
                },
                searchSelected:[],
                searchLoading: true,
                searchList: null,
                searchTotal: 0,

                // 创建与编辑对话框相关设置
                formDialogVisible: false,
                formDialogLoading: false,
                formDialogType:'create',
                formDialogModelReset: null,
                formDialogModel: {// Todo: 修改编辑框的model
<?php
$count = 0;
foreach ($generator->getColumnNames() as $name) {
    if (++$count < 6) {
        echo "                    " . $name . ":'',\n";
    } else {
        echo "                    //" . $name . ":'',\n";
    }
}
?>
                },
                formDialogRule: {// Todo: 修改编辑框的校验规则
                    // xxx: [{ required: true, trigger: 'blur' }],
                    // xxx: [{ type: 'integer', trigger: 'blur' }]
                }
            }
        },
        created() {
            this.OnSearchList()
            this.formDialogModelReset = JSON.parse(JSON.stringify(this.formDialogModel));
        },
        methods: {
            OnSearchList() {
                this.searchLoading = true;
                // Todo:修改获取列表的api函数
                request({
                    url: '/<?=$controllerName ?>/index',
                    method: 'get',
                    params: this.searchModel
                }).then(response => {
                    this.searchList = response.data.items;
                    this.searchTotal = parseInt(response.data.total);

                    this.searchLoading = false
                }).catch(error => {
                    this.$message.error(error.message);
                    this.searchLoading = false;
                });
            },
            OnSearchFilter() {
                this.searchModel.page = 1;
                this.OnSearchList();
            },
            OnSearchSortChange({ column, prop, order }) {
                if(prop && order) {
                    this.searchModel.sort = order == 'ascending' ? '+' + prop : '-' + prop;
                }else{
                    this.searchModel.sort = '-id'; // Todo:默认的排序字段修改
                }
                this.OnSearchList();
            },
            OnSelectRowChange(selection) {
                this.searchSelected = selection;
            },
            OnSelectRowDelete(id) {
                this.$confirm(this.$t('tips.comfirmDeleteWarning'), this.$t('table.warning'),
                    {confirmButtonText: this.$t('table.confirm'),cancelButtonText: this.$t('table.cancel'),center: true,type: 'warning'}).then(() => {
                    let ids = [];

                    id ? ids.push(id) : this.searchSelected.forEach(e => {ids.push(e.id);});

                    if(ids.length === 0) {
                        this.$message.error('Please select your delete row!');
                        return;
                    }

                    this.searchLoading = true;
                    // Todo:修改删除的api函数
                    request({
                        url: '/<?=$controllerName ?>/delete',
                        method: 'post',
                        data: {ids}
                    }).then(() => {
                        this.OnSearchList();
                    }).catch(error => {
                        this.$message.error(error.message);
                        this.searchLoading = false;
                    });
                }).catch(() => {});
            },
            OnPageSizeChange(size) {
                this.searchModel.limit = size;
                this.OnSearchList();
            },
            OnCurrentPageChange(page) {
                this.searchModel.page = page;
                this.OnSearchList();
            },
            OnShowCreateDialog() {
                this.formDialogModel = JSON.parse(JSON.stringify(this.formDialogModelReset));
                this.formDialogType = 'create';
                this.formDialogVisible = true;
                this.$nextTick(() => {this.$refs['formDialogModel'].clearValidate();});
            },
            OnShowUpdateDialog(detail) {
                this.formDialogModel = JSON.parse(JSON.stringify(detail));
                this.formDialogType = 'update';
                this.formDialogVisible = true;
                this.$nextTick(() => {this.$refs['formDialogModel'].clearValidate();});
            },
            OnSubmitFormDialog() {
                this.$refs['formDialogModel'].validate((isvalid) => {
                    if(isvalid){
                        this.formDialogLoading = true;
                        // 提取需要的字段
                        let postdata = {};

                        Object.keys(this.formDialogModelReset).forEach(k => {
                            postdata[k] = this.formDialogModel[k];
                        });

                        // Todo:修改保存的api函数
                        request({
                            url: '/<?=$controllerName ?>/save',
                            method: 'post',
                            data: postdata
                        }).then(response => {
                            this.formDialogVisible = false;
                            this.formDialogLoading = false;
                            this.OnSearchList();
                            this.$message.success('Submit Success!');
                        }).catch(error => {
                            this.formDialogLoading = false;
                            this.$message.error(error.message);
                        });
                    }else{
                        this.$message.error('Please check your input!');
                    }
                });
            }
        }
    }
</script>
