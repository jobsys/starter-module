<template>
    <div class="notification-box bg-white rounded">
        <a-tabs v-model:activeKey="currentTab" class="px-4">
            <a-tab-pane v-for="tab in tabs" :key="tab.key">
                <template #tab>
                    <a-badge :count="tab.unread" :overflow-count="99" :offset="[12, 7]">
                        <span class="px-4">{{ tab.title }} </span>
                    </a-badge>
                </template>
                <NewbieList
                    :ref="(el) => setListRef(el, tab.key)"
                    :height="height"
                    finished-text=""
                    :use-store="notificationMap[tab.key]"
                    :list-props="{ itemLayout: 'horizontal' }"
                    :extra-data="{ type: currentTab }"
                    :url="route('api.manager.starter.notification.items')"
                >
                    <template #renderItem="{ item }">
                        <a-list-item key="item.id">
                            <a-list-item-meta :description="item.data?.message">
                                <template #title>
                                    <a-badge v-if="!item.read_at" color="red"></a-badge>
                                    {{ item.data?.title }}
                                    <span class="text-gray-500 ml-4 text-[12px]">{{ item.created_at_datetime }}</span>
                                </template>
                            </a-list-item-meta>
                            <template #actions v-if="item.data.url">
                                <a-button type="ghost" size="small" @click="onView(item)">
                                    <template #icon>
                                        <EnterOutlined></EnterOutlined>
                                    </template>
                                    查看
                                </a-button>
                            </template>
                            <template #extra>
                                <div>
                                    <a-button type="text" size="small" :disabled="Boolean(item.read_at)" @click="markAsRead(item)">
                                        标记已读
                                    </a-button>
                                    <a-popconfirm title="是否删除当前消息?" @confirm="onDelete(item)">
                                        <a-button type="text" size="small">删除</a-button>
                                    </a-popconfirm>
                                </div>
                            </template>
                        </a-list-item>
                    </template>
                </NewbieList>

                <a-divider class="my-4"></a-divider>
                <div class="pb-4">
                    <a-popconfirm title="是否全部标记已读?" @confirm="markAllAsRead">
                        <a-button type="text">全部{{ tab.title.replace("全部", "") }}标记已读</a-button>
                    </a-popconfirm>
                    <a-popconfirm title="是否全部删除?" @confirm="onDeleteAll">
                        <a-button type="text">删除全部{{ tab.title.replace("全部", "") }}</a-button>
                    </a-popconfirm>
                </div>
            </a-tab-pane>
        </a-tabs>
    </div>
</template>
<script setup>
import { computed, inject, ref } from "vue"
import { EnterOutlined } from "@ant-design/icons-vue"
import { router } from "@inertiajs/vue3"
import { useFetch, useProcessStatusSuccess } from "jobsys-newbie/hooks"
import { message } from "ant-design-vue"

const props = defineProps({
    height: {
        type: Number,
        default: 300,
    },
    useStore: {
        type: Object,
        default: null,
    },
})

const notificationMap = computed(() => props.useStore.notificationMap)

const route = inject("route")
const currentTab = ref("all")
const listRefs = []

const tabs = computed(() => props.useStore.notificationTabs)

const setListRef = (el, key) => {
    listRefs.push({
        key,
        el,
    })
}

const onView = (item) => {
    useFetch().post(route("api.manager.starter.notification.read", { id: item.id }))
    if (item.data?.url) {
        router.visit(item.data.url)
    }
}

const onDelete = async (item) => {
    props.useStore.deleteItem(item, currentTab.value)
    const res = await useFetch().post(route("api.manager.starter.notification.delete", { id: item.id }))
    useProcessStatusSuccess(res, () => {
        props.useStore.deleteItem(item, currentTab.value)
        message.success("删除成功")
    })
}

const markAsRead = async (item) => {
    const res = await useFetch().post(route("api.manager.starter.notification.read", { id: item.id }))
    useProcessStatusSuccess(res, () => {
        props.useStore.read(item, currentTab.value)
        message.success("标记成功")
    })
}

const markAllAsRead = async () => {
    const res = await useFetch().post(route("api.manager.starter.notification.read-all", { type: currentTab.value }))

    useProcessStatusSuccess(res, () => {
        props.useStore.readAll(currentTab.value)
        message.success("标记成功")
    })
}

const onDeleteAll = async () => {
    const res = await useFetch().post(route("api.manager.starter.notification.delete-all", { type: currentTab.value }))

    useProcessStatusSuccess(res, () => {
        props.useStore.deleteAll(currentTab.value)
        message.success("删除成功")
    })
}
</script>
