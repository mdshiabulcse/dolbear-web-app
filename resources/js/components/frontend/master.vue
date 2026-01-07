<template>
    <div>
        <!-- <headNav></headNav> -->
       
        <header_new></header_new>
        <router-view> </router-view>
        <!-- <bottom></bottom> -->

        <!-- <fullHomePage></fullHomePage> -->

        <!-- <bottom></bottom> -->
         <footer_new></footer_new>
    </div>
</template>

<script>
import headNav from "./partials/header";
import bottom from "./partials/bottom";
import fullHomePage from "./homepage/fullHomePage";
import header_new from "./partials/header_new.vue";
import footer_new from "./common/footer_new.vue";


export default {
    name: "master",

    components : {
        headNav,bottom,fullHomePage,
        header_new,
        footer_new
    },
    watch: {
        $route (to, from) {
            if (to.meta.title && to.meta && typeof to.meta.title != 'undefined')
                document.title = to.meta.title
        }
    },
    mounted() {
        document.title = this.$route.meta.title && typeof this.$route.meta.title != 'undefined' ? this.$route.meta.title : this.settings.system_name
    }

}
</script>


