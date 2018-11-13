<template>
    <div class="app-container">
        <!-- 搜索栏 -->
        <div class="filter-container">
            <el-input :placeholder="'ID'" v-model="searchModel.id" style="width: 100px;" class="filter-item"/>

            <el-input :placeholder="$t('system.name')" v-model="searchModel.name" style="width: 200px;" class="filter-item"/>

            <el-select :placeholder="$t('table.status')" v-model="searchModel.status" clearable style="width: 90px" class="filter-item">
                <el-option v-for="item in statusOptions" :key="item.value" :value="item.value" :label="item.label"/>
            </el-select>

            <el-button v-waves class="filter-item" type="primary" icon="el-icon-search" @click="OnSearchFilter">{{ $t('table.search') }}</el-button>
        </div>
        <div class="filter-container">
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
            <el-table-column :label="$t('system.name')" prop="name" sortable="custom" width="150px" align="center">
                <template slot-scope="props">
                    <span>{{ props.row.name }}</span>
                </template>
            </el-table-column>
            <el-table-column :label="$t('table.status')" prop="status" sortable="custom" class-name="status-col" width="100">
                <template slot-scope="props">
                    <el-tag :type="props.row.status == 1 ? 'success' : 'danger'">{{ statusMap[props.row.status]}}</el-tag>
                </template>
            </el-table-column>
            <el-table-column :label="$t('table.actions')" align="center" width="100px">
                <template slot-scope="props">
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
            <el-form ref="formDialogModel" v-loading="formDialogLoading" :model="formDialogModel" :rules="formDialogRule" status-icon>
                <el-form-item label="ID" label-width="100px" v-show="false" prop="id">
                    <el-input v-model="formDialogModel.id" autocomplete="off" readonly="readonly"></el-input>
                </el-form-item>
                <el-form-item :label="$t('system.name')" label-width="100px" prop="name">
                    <el-input v-model="formDialogModel.name" autocomplete="off"></el-input>
                </el-form-item>
                <el-form-item :label="$t('table.status')" label-width="100px" prop="status">
                    <el-select v-model="formDialogModel.status">
                        <el-option v-for="item in statusOptions" :key="item.value" :value="item.value" :label="item.label"/>
                    </el-select>
                </el-form-item>

            </el-form>
            <div slot="footer" class="dialog-footer">
                <el-button @click="formDialogVisible = false">{{$t('table.cancel')}}</el-button>
                <el-button type="primary" :loading="formDialogLoading" @click="OnSubmitFormDialog">{{$t('table.confirm')}}</el-button>
            </div>
        </el-dialog>
    </div>
</template>

<script>
	import { //Todo: 修改导入的api函数
		API_FetchDemoList,
		API_DoDemoSave,
		API_DoDemoDelete
	} from '@/api/system/demo'
	import waves from '@/directive/waves' // 水波纹指令
	import { parseTime } from '@/utils'

	export default {
		name: 'systemDemo', //Todo: 修改名称
		directives: {waves},
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
					id: undefined,
					name: undefined,
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
				formDialogModel: {// Todo: 修改编辑框的model
					id: '',
					name: '',
					status: 1
				},
				formDialogRule: {// Todo: 修改编辑框的校验规则
					name: [{ required: true, trigger: 'blur' }],
					status: [{ type: 'integer', trigger: 'blur' }]
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
				API_FetchDemoList(this.searchModel).then(response => {
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
					API_DoDemoDelete({ids}).then(() => {
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
						API_DoDemoSave(postdata).then(response => {
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