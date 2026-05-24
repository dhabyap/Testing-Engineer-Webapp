export const useApi = () => {
  const config = useRuntimeConfig();
  const apiBaseUrl = config.public.apiBaseUrl;

  const $fetch = globalThis.$fetch.create({
    baseURL: apiBaseUrl,
  });

  return {
    $fetch,
    // Methods untuk setiap resource
    async getCoaCategories() {
      return await $fetch("/coa-categories");
    },
    async createCoaCategory(data) {
      return await $fetch("/coa-categories", { method: "POST", body: data });
    },
    // ... etc
  };
};
