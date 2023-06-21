<template>
    <div class="m-[-20px] bg-gray-100">
        <a-page-header class="bg-white rounded mb-4" title="工作台">
            <div class="flex items-center">
                <div class="w-16 h-16 mr-10">
                    <img class="h-full w-full rounded-full" :src="profile.avatar.url || DefaultAvatar" />
                </div>
                <div class="flex-1">
                    <div class="text-2xl font-bold mb-3">{{ profile.name }}</div>
                    <div class="text-gray-500">
                        <div v-if="departments.length">
                            <span class="mr-2">部门:</span>
                            <a-tag v-for="department in departments" :key="department.name" color="red">
                                {{ department.display_name }}
                            </a-tag>
                        </div>
                        <div v-if="roles.length">
                            <span class="mr-2">角色:</span>
                            <a-tag v-for="role in roles" :key="role.name" color="green">{{ role.display_name }}</a-tag>
                        </div>
                    </div>
                </div>

                <div class="flex stat-items justify-end">
                    <div
                        class="stat-item flex items-center justify-between cursor-pointer hover:bg-gray-100 transition-all"
                        @click="() => router.visit(route('page.manager.project'))"
                    >
                        <div class="bg-gray-50 rounded-full w-16 h-16 flex items-center justify-center mr-4">
                            <NewbieIcon icon="iconfont-projector-line" class="text-blue-300 text-3xl mt-2"></NewbieIcon>
                        </div>
                        <a-statistic
                            title="项目总数"
                            :value="brief.projects_count || 0"
                            :value-style="{ textAlign: 'center' }"
                            suffix="个"
                        ></a-statistic>
                    </div>
                    <div
                        class="stat-item flex items-center justify-between cursor-pointer hover:bg-gray-100 transition-all"
                        @click="() => router.visit(route('page.manager.course'))"
                    >
                        <div class="bg-gray-50 rounded-full w-16 h-16 flex items-center justify-center mr-4">
                            <NewbieIcon icon="iconfont-folder-user-line" class="text-green-300 text-3xl mt-2"></NewbieIcon>
                        </div>
                        <a-statistic
                            title="开班总数"
                            :value="brief.courses_count || 0"
                            :value-style="{ textAlign: 'center' }"
                            suffix="个"
                        ></a-statistic>
                    </div>
                    <div class="stat-item flex items-center justify-between">
                        <div class="bg-gray-50 rounded-full w-16 h-16 flex items-center justify-center mr-4">
                            <NewbieIcon icon="iconfont-team-line" class="text-red-300 text-3xl mt-2"></NewbieIcon>
                        </div>
                        <a-statistic
                            title="学员总数"
                            :value="brief.students_count || 0"
                            :value-style="{ textAlign: 'center' }"
                            suffix="人"
                        ></a-statistic>
                    </div>
                </div>
            </div>
        </a-page-header>

        <a-row :gutter="16">
            <a-col :sm="24" :md="24" :lg="14">Hello World !</a-col>
            <a-col :sm="24" :md="24" :lg="10">
                <NotificationBox :use-store="notificationStore"></NotificationBox>
            </a-col>
        </a-row>
    </div>
</template>

<script setup>
import { useNotificationStore, useUserStore } from "@manager/stores"
import { computed, inject } from "vue"
import DefaultAvatar from "@public/images/default-avatar.png"
import { router } from "@inertiajs/vue3"
import NewbieIcon from "@web/components/NewbieIcon.vue"

import NotificationBox from "@modules/Starter/Resources/views/web/components/NotificationBox.vue"

const userStore = useUserStore()
const notificationStore = useNotificationStore()
const route = inject("route")

defineProps({
    roles: { type: Array, default: () => [] },
    departments: { type: Array, default: () => [] },
})

const profile = computed(() => userStore.profile)
</script>

<style lang="less" scoped>
.stat-items {
    width: 700px;

    .stat-item {
        padding: 10px 32px;
        border-right: 1px solid #f0f0f0;

        &:last-child {
            border-right: none;
        }

        .ant-statistic {
            .ant-statistic-content {
                text-align: center;
            }
        }
    }
}
</style>
