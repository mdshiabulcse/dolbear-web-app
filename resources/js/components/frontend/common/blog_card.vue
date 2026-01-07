<template>
   <div 
  class="blog-card" 
  :style="{ backgroundImage: `url(${blog.thumbnail})` }"
>
    <div class="blog-content">
        <h2>{{ blog.title }}</h2>
        <p>{{ blog.short_description }}</p>
        <router-link class="blog-button" :to="{ name: 'blog.details',params:{slug:blog.slug}}">Details</router-link>
    </div>
</div>


</template>
<script>
export default {
    name: 'blog_card',
    props: {
        blog: {
            type: Object,
            required: true
        }
    },

    methods: {
        
    },
}
</script>

<style>
.blog-card {

    width: 100%;
    max-width: 646px;
    height: 321px;
    border-radius: 8px;
    overflow: hidden;
    /* background-image: 
        linear-gradient(to bottom, rgba(0, 0, 0, 0) 25%, rgba(0, 0, 0, 0.5)),
        url('/images/img/blog.webp'); */
    background-size: cover;
    background-position: center; 
    position: relative;
}


.blog-content {
    position: absolute;
    margin-left: 36px;
    color: white;
    bottom: 36px;
}


.blog-content h2 {
    font-size: 36px;
    font-weight: bold;
    margin-bottom: 5px;
    
}

.blog-content p {
    font-size: 16px;
    margin-bottom: 25px;

}

.blog-button {
    padding: 8px 40px;
    background-color: #1BADEB;
    color: #fff;
    border: none;
    border-radius: 2px;
    cursor: pointer;
    font-weight: 500;
    transition: background-color 0.3s ease;
    text-transform: uppercase;
}

.blog-button:hover {
    background-color: #1592c8;
}

</style>