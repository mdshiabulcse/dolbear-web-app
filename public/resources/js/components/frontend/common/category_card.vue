<template>
    <div class="category-card">
        <img :src="category.all_popular_image" :alt="category.title">
        <router-link :to="{ name: 'product.by.category', params: { slug: category.slug } }">
        <p>{{ category.title }} </p>
       </router-link>
    </div>
</template>
<script>

export default {
    name: 'category_card',
    props: {
        category: {
            type: Object,
            required: true
        }
    }
};
</script>

<style scoped>
.category-card {
    height: 160px;
    width: 156px;
    border-radius: 15px;
    background: #FFFFFF;
    text-align: center;
    padding: 21px;

}

.category-card a {
    color: black;
}
.category-card a:hover {
    color: rgb(83, 53, 255);
}

.category-card img {
    width: 65px;
    height: 65px;
}

.category-card p {
    margin-top: 20px;
    font-size: 16px;
}

@media screen and (max-width: 1024px) {
    .category-card {
        width: 150px;
    }
    
}
@media screen and (max-width: 768px) {
    .category-card {
        width: 134px;
    }
    
}

@media screen and (max-width: 430px) {
    .category-card {
        width: 95px;
    }

    .category-card p {
        font-size: 13px;
    }
}

@media screen and (max-width: 390px) {
    .category-card {
        width: 113px;
    }
}

@media screen and (max-width: 360px) {
    .category-card {
        width: 108px;
    }
}
</style>