<template>
    <div v-if="settings.website_video" class="container mt-3">
        <iframe width="560" height="315" :src="settings.website_video" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" referrerpolicy="strict-origin-when-cross-origin" allowfullscreen></iframe>
    </div>
</template>
<script>
export default {
}
</script>
<style lang="">

</style>