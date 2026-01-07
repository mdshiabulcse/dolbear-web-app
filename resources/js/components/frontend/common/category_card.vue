<template>
    <div class="category-card">
        <img :src="category.all_popular_image" :alt="category.title">
        <router-link :to="{ name: 'product.by.category', params: { slug: category.slug } }">
        <p class="mobile_category_title"> {{ category.title.length > 20 ? category.title.slice(0, 20) + '...' : category.title }}</p>
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
    color: #1BADEB !important;
}

.category-card img {
    width: 40px;
    height: 40px;
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
        height: 100px;
        padding: 10px;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
    }

    .category-card p {
        font-size: 13px;
        margin-top: 5px;
        line-height: 14px;
        color: #212529;
    }

    .category-card p:hover{
        color: #1BADEB;
    }

    .category-card img{
        width: 30px;
        height: 30px;
    }


    /* .category-card {
    height: 135px;
    width: 156px;
    border-radius: 15px;
    background: #FFFFFF;
    text-align: center;
    padding: 21px;
    display: flex;
    justify-content: center;
    align-items: center;
    flex-direction: column;
} */

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