<template>
  <nav aria-label="breadcrumb">
    <ol class="breadcrumb p-1">
      <!-- Home Icon -->
      <span class="mdi mdi-home-account"></span>

      <!-- Dynamic Breadcrumb Items -->
      <li v-for="(breadcrumb, index) in breadcrumbs" :key="index" class="breadcrumb-item">
        <!-- Active breadcrumb (no link) -->
        <router-link v-if="breadcrumb.path" :to="breadcrumb.path">
          {{ breadcrumb.name }}
        </router-link>
        <span v-else class="active" aria-current="page">
          {{ breadcrumb.name }}
        </span>
      </li>
    </ol>
  </nav>
</template>

<script>
export default {
  name: "Breadcrumb",
  props: {
    slug: {
      type: String,
      default: 'product',
    },
  },
  computed: {
    breadcrumbs() {
      // Get the current route path and split it into segments
      const pathSegments = this.$route.path.split('/').filter(Boolean);

      // Build the breadcrumb array
      let breadcrumbPath = '';
      const breadcrumbs = pathSegments.map((segment, index) => {

        if (segment === 'product') {
          if (this.slug) {
            return {
              name: this.formatSegment(this.slug),
              path: `/category/${this.convertToSlug(this.slug)}`,
            };
          }
        }

        if (segment === 'category' ) {
          return {
            name: this.formatSegment(segment),
            path: '/categories',
          };
        }
        if (segment === 'brand') {
          return {
            name: this.formatSegment(segment),
            path: '/products',
          };
        }
        breadcrumbPath += `/${segment}`;
        return {
          name: this.formatSegment(segment), // Format the segment name for display
          path: index < pathSegments.length - 1 ? breadcrumbPath : '', // Only add path if not the last segment
        };
      });

      // Add "Home" as the first breadcrumb
      return [
        { name: this.formatSegment("home"), path: "/" },
        ...breadcrumbs,
      ];
    },
  },
  methods: {
    // Capitalize and format route segments
    formatSegment(segment) {
      return segment
        .replace(/-/g, ' ') // Replace dashes with spaces
        .replace(/\b\w/g, (char) => char.toUpperCase()); // Capitalize each word
    },
    convertToSlug(str) {
      str = str.toLowerCase();
      str = str.replace(/[^a-z0-9 -]/g, '')  
                .replace(/\s+/g, '-')       
                .replace(/-+/g, '-');      

      return str;
    },
  },
};
</script>


<style>
.breadcrumb {
  font-size: 15px;
  margin: 10px 0;
}

.breadcrumb span {
  margin-right: 5px;
}

.breadcrumb-item a {
  color: black;
}

.breadcrumb-item+.breadcrumb-item::before {
  content: ">";
  margin-inline-end: 0px;
}
</style>