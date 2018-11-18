<template>
    <div class="app-container">
        <!-- 搜索栏 -->
        <div class="filter-container">
            <el-input :placeholder="'ID'" v-model="searchModel.id" style="width: 100px;" class="filter-item"/>

            <el-input :placeholder="$t('system.username')" v-model="searchModel.username" style="width: 200px;" class="filter-item"/>

            <el-select :placeholder="$t('table.status')" v-model="searchModel.status" clearable style="width: 90px" class="filter-item">
                <el-option v-for="item in userStatusOptions" :key="item.value" :value="item.value" :label="item.label"/>
            </el-select>

            <el-button class="filter-item" type="primary" icon="el-icon-search" @click="OnSearchFilter">{{ $t('table.search') }}</el-button>

            <el-button class="filter-item" type="success" icon="el-icon-circle-plus-outline" @click="OnShowCreateDialog">{{ $t('table.add') }}</el-button>

            <el-button class="filter-item" type="danger" icon="el-icon-delete" @click="OnSelectRowDelete()">{{ $t('table.batchdelete') }}</el-button>
        </div>

        <!-- table展示列表 -->
        <el-table v-loading="searchLoading" :data="searchList" fit highlight-current-row @sort-change="OnSearchSortChange" @selection-change="OnSelectRowChange" style="width: 100%;">
            <el-table-column type="selection" width="55"></el-table-column>

            <el-table-column label="ID" prop="id" sortable="custom" align="center" width="65px">
                <template slot-scope="props">
                    <span>{{ props.row.id }}</span>
                </template>
            </el-table-column>
            <el-table-column :label="$t('system.username')" prop="username" sortable="custom" width="150px" align="center">
                <template slot-scope="props">
                    <span>{{ props.row.username }}</span>
                </template>
            </el-table-column>
            <el-table-column :label="$t('system.email')" prop="email" sortable="custom" align="center">
                <template slot-scope="props">
                    <span>{{ props.row.email }}</span>
                </template>
            </el-table-column>
            <el-table-column :label="$t('system.role')" prop="role" align="center">
                <template slot-scope="props">
                    <el-tag :type="props.row.role != '' ? 'warning' : 'info'">{{ props.row.role || 'guest' }}</el-tag>
                </template>
            </el-table-column>
            <el-table-column :label="$t('table.status')" prop="status" sortable="custom" class-name="status-col" width="100">
                <template slot-scope="props">
                    <el-tag :type="props.row.status == 10 ? 'success' : 'danger'">{{ userStatusMap[props.row.status]}}</el-tag>
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
            <el-table-column :label="$t('table.actions')" align="center" width="150px">
                <template slot-scope="props">
                    <el-button type="warning" circle size="mini" @click="OnDispathUserRole(props.row)"><svg-icon icon-class="peoples" /></el-button>
                    <el-button type="primary" circle size="mini" icon="el-icon-edit" @click="OnShowUpdateDialog(props.row)"></el-button>
                    <el-button type="danger" circle size="mini" icon="el-icon-delete" @click="OnSelectRowDelete(props.row.id)"></el-button>
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

        <el-dialog :title="formDialogType == 'create' ? $t('system.createUser') : $t('system.updateUser')" :visible.sync="formDialogVisible">
            <el-form ref="formDialogModel" :model="formDialogModel" :rules="formDialogRule" status-icon>
                <el-form-item label="ID" label-width="100px" v-show="false" prop="id">
                    <el-input v-model="formDialogModel.id" autocomplete="off" readonly="readonly"></el-input>
                </el-form-item>
                <el-form-item :label="$t('system.username')" label-width="100px" prop="username">
                    <el-input v-model="formDialogModel.username" autocomplete="off"></el-input>
                </el-form-item>
                <el-form-item :label="$t('system.email')" label-width="100px" prop="email">
                    <el-input v-model="formDialogModel.email" autocomplete="off"></el-input>
                </el-form-item>
                <el-form-item :label="$t('system.password')" label-width="100px" prop="password">
                    <el-input v-model="formDialogModel.password" autocomplete="off"></el-input>
                </el-form-item>
                <el-form-item :label="$t('table.status')" label-width="100px" prop="status">
                    <el-switch v-model="formDialogModel.status" active-value="10" inactive-value="0" active-text="actived" inactive-text="deleted" autocomplete="off"></el-switch>
                </el-form-item>
            </el-form>
            <div slot="footer" class="dialog-footer">
                <el-button @click="formDialogVisible = false">{{$t('table.cancel')}}</el-button>
                <el-button type="primary" :loading="formDialogLoading" @click="OnSubmitFormDialog">{{$t('table.confirm')}}</el-button>
            </div>
        </el-dialog>

        <!-- 角色分配 -->

        <el-dialog :title="$t('system.dispathRole')" :visible.sync="roleDialogVisible">
            <el-form ref="roleDialogModel" :model="roleDialogModel">
                <el-form-item label="ID" label-width="100px" prop="id" v-show="false">
                    <el-input v-model="roleDialogModel.id" :readonly="true" autocomplete="off"></el-input>
                </el-form-item>
                <el-form-item :label="$t('system.username')" label-width="100px" prop="username">
                    <el-input v-model="roleDialogModel.username" :readonly="true" autocomplete="off"></el-input>
                </el-form-item>
                <el-form-item :label="$t('system.role')" label-width="100px" prop="role">
                    <el-select v-model="roleDialogModel.role" autocomplete="off">
                        <el-option v-for="item in roleDialogTotalRoles" :key="item.value" :value="item.value" :label="item.label"/>
                    </el-select>
                </el-form-item>
            </el-form>
            <div slot="footer" class="dialog-footer">
                <el-button @click="roleDialogVisible = false">{{$t('table.cancel')}}</el-button>
                <el-button type="primary" :loading="roleDialogLoading" @click="OnSubmitRoleDialog">{{$t('table.confirm')}}</el-button>
            </div>
        </el-dialog>

    </div>
</template>

<script>
    import request from '@/utils/request'
    import {parseTime} from '@/utils'

	export default {
		name: 'systemUser',
		data() {
			return {
				// 用户状态相关
				userStatusOptions: [
					{'label': 'deleted', 'value' : '0'},
					{'label': 'actived', 'value' : '10'}
                ],
                userStatusMap: {'0':'deleted','10':'actived'},

                // 搜索相关
				searchModel: {
					id: undefined,
					username: undefined,
					status: undefined,
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
				formDialogModel: {
					id: '',
					username: '',
					email: '',
					password: ''
				},
				formDialogRule: {
					username: [{ required: true, trigger: 'blur' }],
					email:    [
						{ required: true, trigger: 'blur' },
                        { type: 'email', trigger: 'blur'}],
                    password: [
                    	{ type: 'string',min:6, trigger: 'blur' }
                    	]
				},

                // 分配角色
				roleDialogVisible: false,
				roleDialogLoading: false,
				roleDialogModel: {
					id: '',
					username: '',
					role: ''
				},
				roleDialogTotalRoles: [],
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
                    url: '/wyrbac/user/index',
                    method: 'get',
                    params: this.searchModel
                }).then(response => {
					this.searchList = response.data.items;
					this.searchTotal = parseInt(response.data.total);

					if(response.data.roles instanceof Array){
						this.roleDialogTotalRoles = [];
						response.data.roles.forEach(e => {
							this.roleDialogTotalRoles.push({value:e,label:e});
						});
					}
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
					this.searchModel.sort = '-id';
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
                    request({
                        url: '/wyrbac/user/delete',
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

                        request({
                            url: '/wyrbac/user/save',
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
            },
			OnDispathUserRole(detail) {
				this.roleDialogModel = {
					id: detail.id,
					username: detail.username,
					role: detail.role || ''
				};
                this.roleDialogVisible = true;
            },
			OnSubmitRoleDialog() {
                this.roleDialogLoading = true;
                request({
                    url: '/wyrbac/user/save-role',
                    method: 'post',
                    data: this.roleDialogModel
                }).then(response => {
					this.roleDialogLoading = false;
					this.roleDialogVisible = false;
					this.OnSearchList();
					this.$message.success('Submit Success!');
                }).catch(error => {
					this.roleDialogLoading = false;
					this.$message.error(error.message);
				});
            },
            DateFormat(date) {
                return parseTime(date,'{y}-{m}-{d} {h}:{i}:{s}')
            }
		}
	}
</script>