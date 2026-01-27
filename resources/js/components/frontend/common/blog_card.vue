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
    min-height: 280px;
    height: auto;
    border-radius: 8px;
    overflow: hidden;
    background-size: cover;
    background-position: center;
    position: relative;
    display: flex;
    align-items: flex-end;
}

.blog-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(to bottom, rgba(0, 0, 0, 0) 0%, rgba(0, 0, 0, 0.3) 50%, rgba(0, 0, 0, 0.7) 100%);
    z-index: 1;
}

.blog-content {
    position: relative;
    padding: 24px;
    color: white;
    z-index: 2;
    width: 100%;
}

.blog-content h2 {
    font-size: 24px;
    font-weight: bold;
    margin-bottom: 8px;
    line-height: 1.3;
    max-width: 90%;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.blog-content p {
    font-size: 14px;
    margin-bottom: 20px;
    line-height: 1.5;
    max-width: 85%;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.blog-button {
    padding: 10px 32px;
    background-color: #1BADEB;
    color: #fff;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    font-weight: 500;
    transition: background-color 0.3s ease;
    text-transform: uppercase;
    font-size: 14px;
    white-space: nowrap;
    display: inline-block;
}

.blog-button:hover {
    background-color: #1592c8;
}

/* Tablet responsive */
@media screen and (max-width: 991px) {
    .blog-card {
        min-height: 260px;
    }

    .blog-content {
        padding: 20px;
    }

    .blog-content h2 {
        font-size: 20px;
        max-width: 95%;
    }

    .blog-content p {
        font-size: 13px;
        margin-bottom: 16px;
        max-width: 90%;
    }

    .blog-button {
        padding: 8px 28px;
        font-size: 13px;
    }
}

/* Mobile responsive */
@media screen and (max-width: 767px) {
    .blog-card {
        min-height: 240px;
    }

    .blog-content {
        padding: 16px;
        bottom: 16px;
    }

    .blog-content h2 {
        font-size: 18px;
        margin-bottom: 6px;
        line-height: 1.4;
    }

    .blog-content p {
        font-size: 12px;
        margin-bottom: 12px;
        line-height: 1.4;
    }

    .blog-button {
        padding: 8px 24px;
        font-size: 12px;
    }
}

/* Small mobile responsive */
@media screen and (max-width: 480px) {
    .blog-card {
        min-height: 220px;
    }

    .blog-content {
        padding: 12px;
    }

    .blog-content h2 {
        font-size: 16px;
        margin-bottom: 4px;
        -webkit-line-clamp: 3;
    }

    .blog-content p {
        font-size: 11px;
        margin-bottom: 10px;
        -webkit-line-clamp: 2;
    }

    .blog-button {
        padding: 6px 20px;
        font-size: 11px;
    }
}

/* Extra small mobile */
@media screen and (max-width: 360px) {
    .blog-card {
        min-height: 200px;
    }

    .blog-content {
        padding: 10px;
    }

    .blog-content h2 {
        font-size: 14px;
        margin-bottom: 4px;
        -webkit-line-clamp: 3;
    }

    .blog-content p {
        font-size: 10px;
        margin-bottom: 8px;
        -webkit-line-clamp: 2;
    }

    .blog-button {
        padding: 6px 16px;
        font-size: 10px;
    }
}

</style>