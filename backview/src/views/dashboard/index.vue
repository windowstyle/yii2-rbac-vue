<template>
    <div class="dashboard-container">
        <component :is="currentRole"/>
    </div>
</template>

<script>
    import { mapGetters } from 'vuex'
    import adminDashboard from './admin'
    import normalDashboard from './normal'

    export default {
        name: 'Dashboard',
        components: {
            adminDashboard,
            normalDashboard
        },
        data() {
            return {
                currentRole: 'normalDashboard'
            }
        },
        computed: {
            ...mapGetters([
                'roles'
            ])
        },
        created() {
            // 审核员之类的角色，展示普通的首页，管理员角色可以展示一些核心的报表数据
            if(this.roles.includes('auditor')){
                this.currentRole = 'normalDashboard'
            }else{
                this.currentRole = 'adminDashboard'
            }
        }
    }
</script>
