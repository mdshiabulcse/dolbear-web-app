<template>
    <div class="container">
      <div class="row mt-2">
   
          <div v-for="blog in blogs" :key="blog.id"class="col-12 col-md-6 col-lg-6 mb-2 mb-md-0"> 
            <blog_card :blog="blog"></blog_card>
        
        </div>
      </div>
    </div>
  </template>
  
<script>
import blog_card from '../../common/blog_card.vue';
export default {    
    components: {
        blog_card
    },
    data() {
        return {
            blogs: [],
        }
    },

    async created() {
        await this.loadNewBlogs();
    },

    methods: {
        async loadNewBlogs() {

        const baseUrl = `${window.location.protocol}//${window.location.host}`;

        const url = `${baseUrl}/home/blogs`;

        axios.get(url).then((response) => {
            if (response.data.error) {
                toastr.error(response.data.error, this.lang.Error + " !!");
            } else {
                this.blogs = response.data.blogs.data;

                this.blogs = this.blogs.slice(-2);

            }
        });
        }
    }
}
</script>
<style >
    
</style>