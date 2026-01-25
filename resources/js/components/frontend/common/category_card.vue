<template>
    <router-link :to="{ name: 'product.by.category', params: { slug: category.slug } }" class="category-card-link">
        <div class="category-card">
            <div class="category-card-content">
                <img :src="category.all_popular_image" :alt="category.title">
                <p class="mobile_category_title"> {{ category.title.length > 20 ? category.title.slice(0, 20) + '...' : category.title }}</p>
            </div>
        </div>
    </router-link>
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
.category-card-link {
    text-decoration: none;
    display: block;
}

.category-card {
    height: 160px;
    width: 156px;
    border-radius: 15px;
    background: #FFFFFF;
    padding: 21px;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.category-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.category-card-content {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    text-align: center;
    width: 100%;
    height: 100%;
}

.category-card a {
    color: black;
    text-decoration: none;
}

.category-card a:hover {
    color: #1BADEB !important;
}

.category-card img {
    width: 40px;
    height: 40px;
    object-fit: contain;
    display: block;
    margin: 0 auto;
}

.category-card p {
    margin: 15px 0 0 0;
    font-size: 16px;
    font-weight: 500;
    color: #212529;
    text-align: center;
    width: 100%;
    word-wrap: break-word;
}

/* Medium screens - tablets */
@media screen and (max-width: 1024px) {
    .category-card {
        width: 140px;
        height: 145px;
        padding: 18px;
    }

    .category-card img {
        width: 36px;
        height: 36px;
    }

    .category-card p {
        font-size: 15px;
        margin-top: 12px;
    }
}

@media screen and (max-width: 768px) {
    .category-card {
        width: 125px;
        height: 130px;
        padding: 15px;
    }

    .category-card img {
        width: 34px;
        height: 34px;
    }

    .category-card p {
        font-size: 14px;
        margin-top: 10px;
    }
}

/* Large mobile */
@media screen and (max-width: 576px) {
    .category-card {
        width: 110px;
        height: 115px;
        padding: 12px;
    }

    .category-card img {
        width: 32px;
        height: 32px;
    }

    .category-card p {
        font-size: 13px;
        margin-top: 8px;
    }
}

/* Small mobile */
@media screen and (max-width: 430px) {
    .category-card {
        width: 95px;
        height: 100px;
        padding: 10px;
    }

    .category-card img {
        width: 30px;
        height: 30px;
    }

    .category-card p {
        font-size: 12px;
        margin-top: 6px;
        line-height: 14px;
    }

    .category-card p:hover {
        color: #1BADEB;
    }
}

@media screen and (max-width: 390px) {
    .category-card {
        width: 88px;
        height: 95px;
        padding: 8px;
    }

    .category-card img {
        width: 28px;
        height: 28px;
    }

    .category-card p {
        font-size: 11px;
        margin-top: 5px;
    }
}

@media screen and (max-width: 360px) {
    .category-card {
        width: 82px;
        height: 90px;
        padding: 8px;
    }

    .category-card img {
        width: 26px;
        height: 26px;
    }

    .category-card p {
        font-size: 11px;
        margin-top: 4px;
    }
}
</style>