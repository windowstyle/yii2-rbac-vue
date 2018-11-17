<template>
    <div class="app-container">
        <!-- 搜索栏 -->
        <div class="filter-container">
            <el-input :placeholder="$t('system.name')" v-model="searchModel.name" style="width: 200px;" class="filter-item"/>

            <el-button class="filter-item" type="primary" icon="el-icon-search" @click="OnSearchFilter">{{ $t('table.search') }}</el-button>

            <el-button class="filter-item" style="margin-left: 10px;" type="success" icon="el-icon-circle-plus-outline" @click="OnShowCreateDialog">{{ $t('table.add') }}</el-button>

            <el-button class="filter-item" type="danger" icon="el-icon-delete" @click="OnSelectRowDelete()">{{ $t('table.batchdelete') }}</el-button>

            <el-button class="filter-item" type="warning" icon="el-icon-refresh" @click="OnRefreshRoutes">{{ $t('system.refreshroutes') }}</el-button>
        </div>

        <!-- table展示列表 -->
        <el-table v-loading="searchLoading" :data="searchList" :key="searchTableKey" fit highlight-current-row @sort-change="OnSearchSortChange" @selection-change="OnSelectRowChange" style="width: 100%;">
            <el-table-column type="selection" width="55"></el-table-column>

            <el-table-column :label="$t('system.name')" prop="name" sortable="custom" align="center">
                <template slot-scope="props">
                    <span>{{ props.row.name }}</span>
                </template>
            </el-table-column>
            <el-table-column :label="$t('system.description')" prop="description" sortable="custom" align="center">
                <template slot-scope="props">
                    <span>{{ props.row.description }}</span>
                </template>
            </el-table-column>
            <el-table-column :label="$t('table.addtime')" prop="created_at" sortable="custom" align="center">
                <template slot-scope="props">
                    <span>{{DateFormat(props.row.created_at,'{y}-{m}-{d} {h}:{i}:{s}') }}</span>
                </template>
            </el-table-column>
            <el-table-column :label="$t('table.updatetime')" prop="updated_at" sortable="custom" align="center">
                <template slot-scope="props">
                    <span>{{DateFormat(props.row.updated_at,'{y}-{m}-{d} {h}:{i}:{s}') }}</span>
                </template>
            </el-table-column>
            <el-table-column :label="$t('table.actions')" align="center" width="200px">
                <template slot-scope="props">
                    <el-button type="success" circle size="mini" @click="OnDispathPermission(props.row)"><svg-icon icon-class="tree" /></el-button>
                    <el-button type="primary" circle size="mini" icon="el-icon-edit" @click="OnShowUpdateDialog(props.row)"></el-button>
                    <el-button type="danger" circle size="mini" icon="el-icon-delete" @click="OnSelectRowDelete(props.row.name)"></el-button>
                </template>
            </el-table-column>
        </el-table>

        <div class="pagination-container">
            <el-pagination v-show="searchTotal > 0"
                           :current-page="searchModel.page"
                           :page-sizes="[10,20,30, 50]"
                           :page-size="searchModel.limit"
                           :total="searchTotal"
                           @size-change="OnPageSizeChange"
                           @current-change="OnCurrentPageChange"
                           background layout="total, sizes, prev, pager, next, jumper"/>
        </div>

        <!-- 编辑框 -->

        <el-dialog :title="formDialogType == 'create' ? $t('system.createrole') : $t('system.updaterole')" :visible.sync="formDialogVisible">
            <el-form ref="formDialogModel" :model="formDialogModel" :rules="formDialogRule" status-icon>
                <el-form-item :label="$t('system.name')" label-width="100px" prop="name">
                    <el-input v-model="formDialogModel.name" :readonly="formDialogType == 'update'" autocomplete="off"></el-input>
                </el-form-item>
                <el-form-item :label="$t('system.description')" label-width="100px" prop="description">
                    <el-input v-model="formDialogModel.description" autocomplete="off"></el-input>
                </el-form-item>
            </el-form>
            <div slot="footer" class="dialog-footer">
                <el-button @click="formDialogVisible = false">{{$t('table.cancel')}}</el-button>
                <el-button type="primary" :loading="formDialogLoading" @click="OnSubmitFormDialog">{{$t('table.confirm')}}</el-button>
            </div>
        </el-dialog>

        <!-- 权限分配 -->

        <el-dialog :title="$t('system.dispathPermission')" :visible.sync="permissionDialogVisible">
            <el-form ref="permissionDialogModel" :model="permissionDialogModel">
                <el-form-item :label="$t('system.name')" label-width="100px" prop="name">
                    <el-input v-model="permissionDialogModel.name" :readonly="true" autocomplete="off"></el-input>
                </el-form-item>
                <el-form-item :label="$t('system.permission')" label-width="100px" prop="permissions">
                    <template>
                        <el-transfer v-model="permissionDialogModel.permissions"
                             :data="permissionTotalRoutes" :titles="[$t('table.source'), $t('table.target')]"
                             :filter-placeholder="$t('system.permission')" filterable></el-transfer>
                    </template>
                </el-form-item>
            </el-form>
            <div slot="footer" class="dialog-footer">
                <el-button @click="permissionDialogVisible = false">{{$t('table.cancel')}}</el-button>
                <el-button type="primary" :loading="permissionDialogLoading" @click="OnSubmitPermissionDialog">{{$t('table.confirm')}}</el-button>
            </div>
        </el-dialog>

    </div>
</template>

<script>
    import request from '@/utils/request'
    import {parseTime} from '@/utils'

	export default {
		name: 'systemRole',
		data() {
			return {
				// 搜索相关
				searchModel: {
					name: undefined,
					page: 1,
					limit: 10,
					sort: '+name'
				},
				searchSelected:[],
				searchLoading: true,
				searchList: null,
				searchTableKey:0,
				searchTotal: 0,

				// 创建与编辑对话框相关设置
				formDialogVisible: false,
				formDialogLoading: false,
				formDialogType:'create',
				formDialogModelReset: null,
				formDialogModel: {
					name: '',
					description: '',
				},
				formDialogRule: {
					name: [{ required: true, trigger: 'blur' }],
					type:    [
						{ required: true, trigger: 'blur' },
						{ type: 'enum',enum : [1,2], trigger: 'blur'}],
					description: [
						{ type: 'string',max:255, trigger: 'blur' }
					]
				},

				// 权限分配
				permissionDialogVisible: false,
				permissionDialogLoading: false,
				permissionDialogModel: {
					name:'',
                    permissions: []
                },
                permissionTotalRoutes: [],
			}
		},
		created() {
			this.OnSearchList()
			this.formDialogModelReset = JSON.parse(JSON.stringify(this.formDialogModel));
		},
		methods: {
			OnSearchList() {
				this.searchLoading = true
                request({
                    url: '/wyvue/auth/index',
                    method: 'get',
                    params: this.searchModel
                }).then(response => {
					this.searchList = response.data.items
					this.searchTotal = parseInt(response.data.total)

                    if(response.data.routes instanceof Array){
						this.permissionTotalRoutes = [];
						response.data.routes.forEach(e => {
							this.permissionTotalRoutes.push({key:e,label:e});
                        });
                    }
					this.searchLoading = false;
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
					this.searchModel.sort = '+name';
                }

				this.OnSearchList();
			},
			OnSelectRowChange(selection) {
				this.searchSelected = selection;
			},
			OnSelectRowDelete(name) {
				this.$confirm(this.$t('tips.comfirmDeleteWarning'), this.$t('table.warning'),
					{confirmButtonText: this.$t('table.confirm'),cancelButtonText: this.$t('table.cancel'),center: true,type: 'warning'}).then(() => {
					let names = [];

					name ? names.push(name) : this.searchSelected.forEach(e => {names.push(e.name);});

					if(names.length === 0) {
						this.$message.error('Please select your delete row!');
						return;
					}

					this.searchLoading = true;
                    request({
                        url: '/wyvue/auth/delete',
                        method: 'post',
                        data: names
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

                        request({
                            url: '/wyvue/auth/save',
                            method: 'post',
                            data: postdata
                        }).then(response => {
							this.formDialogVisible = false;
							this.formDialogLoading = false;
							this.OnSearchList();
							this.$message.success('Submit Success!');
						}).catch(error => {
							this.$message.error(error.message);
							this.formDialogLoading = false;
						});
					}else{
						this.$message.error('Please check your input!');
					}
				});
			},
			OnRefreshRoutes() {
				this.searchLoading = true;
                request({
                    url: '/wyvue/auth/refresh-routes',
                    method: 'get'
                }).then(() => {
					this.OnSearchList();
					this.$message.success('Refresh Success!');
                }).catch(error => {
					this.$message.error(error.message);
					this.searchLoading = false;
				});
            },
            OnDispathPermission(detail) {
                this.permissionDialogModel = {
                	name:detail.name,
                	permissions:detail.permissions
				};
				this.permissionDialogVisible = true;
            },
			OnSubmitPermissionDialog() {
				this.permissionDialogLoading = true;
                request({
                    url: '/wyvue/auth/save-permissions',
                    method: 'post',
                    data: this.permissionDialogModel
                }).then(() => {
				    this.permissionDialogVisible = false;
					this.permissionDialogLoading = false;
					this.OnSearchList();
					this.$message.success('Submit Success!');
				}).catch(error => {
					this.$message.error(error.message);
					this.permissionDialogLoading = false;
				});
            },
            DateFormat(date) {
                return parseTime(date,'{y}-{m}-{d} {h}:{i}:{s}')
            }
		}
	}
</script>