import { defineStore } from "pinia";
import apiClient from "@/axios";
import { useLoadingStore } from "./loading";
import { useResponseStore } from "./response";

export const useTransactionStore = defineStore("transaction", {
  state: () => ({
    paginatedTransactions: [],
    totalTransactions: 0,
    search: "",
    selectedTransactionId: null,
    selectedType: null,
    dateRange: [
      new Date(new Date().setHours(9, 0, 0, 0)),
      new Date(new Date().setHours(17, 0, 0, 0)),
    ],

    page: 1,
    itemsPerPage: 10,
    sortBy: "date",
    order: "asc",
    types: [],
  }),
  actions: {
    updateOptions({ page, itemsPerPage, sortBy }) {
      this.page = page;
      this.itemsPerPage = itemsPerPage;
      this.sortBy = sortBy;

      this.fetchPaginatedTransactions();
    },

    async fetchPaginatedTransactions() {
      const loadingStore = useLoadingStore();
      const responseStore = useResponseStore();
      loadingStore.startLoading("fetchPaginatedTransactions");

      try {
        const { data } = await apiClient.get("/paginated-transactions", {
          params: {
            page: this.page,
            itemsPerPage: this.itemsPerPage,
            sortBy: this.sortBy,
            order: this.order,
            search: this.search,
            type: this.selectedType,
            startDate: this.dateRange[0].toISOString(),
            endDate: this.dateRange[this.dateRange.length - 1].toISOString(),
          },
        });
        this.paginatedTransactions = data.transactions;

        this.totalTransactions = data.total;
      } catch (error) {
        responseStore.setResponse(false, error.response.data.message, [
          error.response.data.errors,
        ]);
      } finally {
        loadingStore.stopLoading("fetchPaginatedTransactions");
      }
    },

    async fetchTypes() {
      const loadingStore = useLoadingStore();
      const responseStore = useResponseStore();
      loadingStore.startLoading("fetchTypes");

      try {
        const { data } = await apiClient.get("/types", {});
        this.types = data.types;
      } catch (error) {
        responseStore.setResponse(false, error.response.data.message, [
          error.response.data.errors,
        ]);
      } finally {
        loadingStore.stopLoading("fetchTypes");
      }
    },

    resetFilters() {
      this.search = "";
      this.selectedType = null;
      this.selectedTransactionId = null;
      // Default start date with 9 AM
      // Default end date with 5 PM
      this.dateRange = [
        new Date(new Date().setHours(9, 0, 0, 0)),
        new Date(new Date().setHours(17, 0, 0, 0)),
      ];

      this.fetchPaginatedTransactions();
    },

    async createTransaction(data) {
      const responseStore = useResponseStore();
      const loadingStore = useLoadingStore();
      loadingStore.startLoading("createTransaction");

      try {
        const response = await apiClient.post("/transactions", data, {
          headers: { "Content-Type": "multipart/form-data" },
        });
        this.selectedTransactionId = response.transcation.id;
        this.fetchPaginatedTransactions();
        responseStore.setResponse(true, "Type created successfully");
      } catch (error) {
        responseStore.setResponse(false, error.response.data.message, [
          error.response.data.errors,
        ]);
      } finally {
        loadingStore.stopLoading("createTransaction");
      }
    },

    async updateTransaction(data) {
      const responseStore = useResponseStore();
      const loadingStore = useLoadingStore();
      loadingStore.startLoading("updateTransaction");

      try {
        // Create a new FormData object
        const formData = new FormData();

        // Append all item data fields to formData
        for (const [key, value] of data.entries()) {
          formData.append(key, value);
        }

        // Send POST request with FormData
        const response = await apiClient.post(
          `/update-transaction/${data.get("id")}`,
          formData,
          {
            headers: { "Content-Type": "multipart/form-data" },
          }
        );

        this.fetchPaginatedTransactions();

        responseStore.setResponse(true, "Transaction updated successfully");
      } catch (error) {
        responseStore.setResponse(false, error.response.data.message, [
          error.response.data.errors,
        ]);
      } finally {
        loadingStore.stopLoading("updateTransaction");
      }
    },

    async deleteTransaction(transactionId) {
      const responseStore = useResponseStore();
      const loadingStore = useLoadingStore();
      loadingStore.startLoading("deleteTransaction");

      try {
        await apiClient.delete(`/transaction/${transactionId}`);
        this.userTypes = this.userTypes.filter(
          (transaction) => transaction.id !== transactionId
        );
        this.selectedTransactionId = null;
        this.fetchPaginatedTransactions();
        responseStore.setResponse(true, "Transaction deleted successfully");
      } catch (error) {
        responseStore.setResponse(false, error.response.data.message, [
          error.response.data.errors,
        ]);
      } finally {
        loadingStore.stopLoading("deleteTransaction");
      }
    },
  },
});
