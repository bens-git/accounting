<template>
  <v-container class="d-flex justify-center" v-if="transactionStore.users">
    <v-card
      v-if="transactionStore.transactions"
      title="Monthly Summary"
      flat
      style="
        overflow-y: scroll;
        min-width: 90vw;
        max-height: 88vh;
        min-height: 88vh;
      "
    >
      <!-- Card Content -->
      <v-card-text>
        <v-row>
          <v-col cols="12" md="6">
            <!-- Month Picker -->
            <v-select
              density="compact"
              v-model="transactionStore.selectedMonth"
              :items="transactionStore.months"
              label="Month"
              outlined
              class="me-4"
            />
          </v-col>
          <v-col cols="12" md="6">
            <!-- Year Picker -->
            <v-select
              density="compact"
              v-model="transactionStore.selectedYear"
              :items="years"
              label="Year"
              outlined
            />
          </v-col>
        </v-row>

        <div
          v-if="transactionStore.selectedMonth && transactionStore.selectedYear"
        >
          <div v-for="user in transactionStore.users" :key="user.id">
            <v-data-table
              v-if="userMaintenanceTransactions(user.id, 'PURCHASE').length"
              :items="userMaintenanceTransactions(user.id, 'PURCHASE')"
              :items-per-page="-1"
              density="compact"
              :headers="[
                { title: 'Name', value: 'name' },
                { title: 'Amount', value: 'amount', align: 'end' },
                { title: '', value: 'empty1' },
                { title: '', value: 'empty2' },
              ]"
              :hide-default-footer="true"
              class="mb-6"
            >
              <!-- Table Header -->
              <template v-slot:top>
                <v-toolbar flat>
                  <v-toolbar-title
                    >{{ user.name }}'s Maintenance Purchases</v-toolbar-title
                  >
                  <v-spacer></v-spacer>
                </v-toolbar>
              </template>

              <!-- Table Body -->
              <template v-slot:body="{ items }">
                <tr v-for="item in items" :key="item.id">
                  <td>{{ item.name }}</td>
                  <td class="text-end">{{ formatMoney(item.amount) }}</td>
                  <td></td>
                  <td></td>
                </tr>
              </template>

              <!-- Table Footer -->
              <template v-slot:body.append>
                <tr>
                  <td class="text-end font-weight-bold">Total:</td>
                  <td class="text-end">
                    {{ formatMoney(totalMaintenancePurchases(user.id)) }}
                  </td>
                  <td class="text-end">
                    {{
                      "Total Owed To " +
                      owedToTextBased(
                        totalMaintenancePurchases(user.id),
                        user.name
                      ) +
                      ": (" +
                      otherPercent(user.maintenance_percentage) +
                      " x " +
                      totalMaintenancePurchases(user.id) +
                      ")"
                    }}
                  </td>
                  <td class="text-end">
                    {{
                      formatMoney(
                        totalMaintenancePurchases(user.id) *
                          otherPercent(user.maintenance_percentage)
                      )
                    }}
                  </td>
                </tr>
              </template>
            </v-data-table>

            <v-data-table
              v-if="userMaintenanceTransactions(user.id, 'BILL').length"
              :items="userMaintenanceTransactions(user.id, 'BILL')"
              :items-per-page="-1"
              density="compact"
              :headers="[
                { title: 'Name', value: 'name' },
                { title: 'Amount', value: 'amount', align: 'end' },
                { title: '', value: 'empty1' },
                { title: '', value: 'empty2' },
              ]"
              :hide-default-footer="true"
              class="mb-6"
            >
              <!-- Table Header -->
              <template v-slot:top>
                <v-toolbar flat>
                  <v-toolbar-title
                    >{{ user.name }}'s Maintenance Bills</v-toolbar-title
                  >
                  <v-spacer></v-spacer>
                </v-toolbar>
              </template>

              <!-- Table Body -->
              <template v-slot:body="{ items }">
                <tr v-for="item in items" :key="item.id">
                  <td>{{ item.name }}</td>
                  <td class="text-end">{{ formatMoney(item.amount) }}</td>
                  <td></td>
                  <td></td>
                </tr>
              </template>

              <template v-slot:body.append>
                <tr>
                  <td class="text-end font-weight-bold">Total:</td>

                  <td class="text-end">
                    {{ formatMoney(totalMaintenanceBills(user.id)) }}
                  </td>

                  <td class="text-end">
                    {{
                      "Total Owed To " +
                      owedToTextBased(
                        totalMaintenanceBills(user.id),
                        user.name
                      ) +
                      ": (" +
                      otherPercent(user.maintenance_percentage) +
                      " x " +
                      totalMaintenanceBills(user.id) +
                      ")"
                    }}
                  </td>

                  <td class="text-end">
                    {{
                      formatMoney(
                        totalMaintenanceBills(user.id) *
                          otherPercent(user.maintenance_percentage)
                      )
                    }}
                  </td>
                </tr>
              </template>
            </v-data-table>

            <v-data-table
              v-if="userMaintenanceTransactions(user.id, 'INCOME').length"
              :items="userMaintenanceTransactions(user.id, 'INCOME')"
              :items-per-page="-1"
              density="compact"
              :headers="[
                { title: 'Name', value: 'name' },
                { title: 'Amount', value: 'amount', align: 'end' },
                { title: '', value: 'empty1' },
                { title: '', value: 'empty2' },
              ]"
              :hide-default-footer="true"
              class="mb-6"
            >
              <!-- Table Header -->
              <template v-slot:top>
                <v-toolbar flat>
                  <v-toolbar-title
                    >{{ user.name }}'s Maintenance Incomes</v-toolbar-title
                  >
                  <v-spacer></v-spacer>
                </v-toolbar>
              </template>

              <!-- Table Body -->
              <template v-slot:body="{ items }">
                <tr v-for="item in items" :key="item.id">
                  <td>{{ item.name }}</td>
                  <td class="text-end">{{ formatMoney(item.amount) }}</td>
                  <td></td>
                  <td></td>
                </tr>
              </template>

              <!-- Table Footer -->
              <template v-slot:body.append>
                <tr>
                  <td class="text-end font-weight-bold">Total:</td>

                  <td class="text-end">
                    {{ formatMoney(totalMaintenanceIncomes(user.id)) }}
                  </td>

                  <td class="text-end">
                    {{
                      "Total Owed To " +
                      otherUser(
                        owedToTextBased(
                          totalMaintenanceIncomes(user.id),
                          user.name
                        )
                      ) +
                      ": (" +
                      otherPercent(user.maintenance_percentage) +
                      " x " +
                      totalMaintenanceIncomes(user.id) +
                      ")"
                    }}
                  </td>

                  <td class="text-end">
                    {{
                      formatMoney(
                        totalMaintenanceIncomes(user.id) *
                          otherPercent(user.maintenance_percentage)
                      )
                    }}
                  </td>
                </tr>
              </template>
            </v-data-table>

            <v-data-table
              v-if="userGiftTransactions(user.id).length"
              :items="userGiftTransactions(user.id)"
              :items-per-page="-1"
              density="compact"
              :headers="[
                { title: 'Name', value: 'name' },
                { title: 'Amount', value: 'amount', align: 'end' },
                { title: '', value: 'empty1' },
                { title: '', value: 'empty2' },
              ]"
              :hide-default-footer="true"
              class="mb-6"
            >
              <!-- Table Header -->
              <template v-slot:top>
                <v-toolbar flat>
                  <v-toolbar-title>
                    {{ user.name }}'s Purchases For
                    {{ otherUser(user.name) }}</v-toolbar-title
                  >
                  <v-spacer></v-spacer>
                </v-toolbar>
              </template>

              <template v-slot:body="{ items }">
                <tr v-for="item in items" :key="item.id">
                  <td>{{ item.name }}</td>

                  <td class="text-end">{{ formatMoney(item.amount) }}</td>

                  <td></td>
                  <td></td>
                </tr>
              </template>

              <template v-slot:body.append>
                <tr>
                  <td class="text-end font-weight-bold">Total:</td>

                  <td class="text-end">
                    {{ formatMoney(totalGifts(user.id)) }}
                  </td>

                  <td class="text-end">
                    {{
                      "Total Owed To " +
                      owedToTextBased(totalGifts(user.id), user.name) +
                      ":"
                    }}
                  </td>

                  <td class="text-end">
                    {{ formatMoney(totalGifts(user.id)) }}
                  </td>
                </tr>
              </template>
            </v-data-table>
          </div>

          <v-toolbar flat>
            <v-toolbar-title>Summary</v-toolbar-title>
          </v-toolbar>
          <h3 class="ma-4">
            {{ "Total Owed To " + whoOwesWho(calculation?.value()) + ": " }}
            {{ calculation?.format("($0.00)") }}
          </h3>
        </div>
        <v-alert color="warning" v-else>
          Please select a month and year
        </v-alert>
      </v-card-text>
    </v-card>
  </v-container>
</template>

<script setup>
import { computed, watch, onMounted } from "vue";
import { useTransactionStore } from "@/stores/transaction";
import numeral from "numeral";

const transactionStore = useTransactionStore();

onMounted(() => {
  const now = new Date();
  if (!transactionStore.selectedMonth) {
    transactionStore.selectedMonth = transactionStore.months[now.getMonth()];
  } // Current month
  if (!transactionStore.selectedYear) {
    transactionStore.selectedYear = now.getFullYear();
  } // Current year

  transactionStore.itemsPerPage = -1;
  transactionStore.search = null;
  transactionStore.selectedType = null;
  transactionStore.selectedTag = null;
  transactionStore.fetchUsers();
  transactionStore.fetchTransactions();
});

watch(
  computed(() => [
    transactionStore.selectedMonth,
    transactionStore.selectedYear,
  ]),
  async () => {
    transactionStore.fetchTransactions();
  }
);

const years = generateYears();

function generateYears() {
  const currentYear = new Date().getFullYear();
  const range = 20; // Adjust the range of years as needed
  return Array.from({ length: range }, (_, i) => currentYear - 10 + i);
}

function totalMaintenancePurchases(userId) {
  return totalOfTransactions(
    transactionStore.transactions.filter(function (transaction) {
      return (
        transaction.user_id == userId &&
        transaction.tag == "MAINTENANCE" &&
        transaction.type == "PURCHASE"
      );
    })
  );
}

function totalMaintenanceBills(userId) {
  return totalOfTransactions(
    transactionStore.transactions.filter(function (transaction) {
      return (
        transaction.user_id == userId &&
        transaction.tag == "MAINTENANCE" &&
        transaction.type == "BILL"
      );
    })
  );
}
function totalMaintenanceIncomes(userId) {
  return totalOfTransactions(
    transactionStore.transactions.filter(function (transaction) {
      return (
        transaction.user_id == userId &&
        transaction.tag == "MAINTENANCE" &&
        transaction.type == "INCOME"
      );
    })
  );
}
function whoOwesWho(amount) {
  const ben = transactionStore.users.find((user) => user.id === 1);
  const melinda = transactionStore.users.find((user) => user.id === 2);
  if (!ben) {
    return null;
  }
  if (amount > 0) {
    return ben.name;
  } else {
    return melinda.name;
  }
}
function totalGifts(userId) {
  return totalOfTransactions(
    transactionStore.transactions.filter(function (transaction) {
      return (
        transaction.type == "PURCHASE" &&
        transaction.user_id == userId &&
        transaction.recipient_id
      );
    })
  );
}

const calculation = computed(() => {
  const ben = transactionStore.users.find((user) => user.id === 1);
  const melinda = transactionStore.users.find((user) => user.id === 2);
  if (!ben) {
    return null;
  }
  let total = numeral(0);
  let total1 = numeral(0);

  total1 = total1.add(totalMaintenancePurchases(1));

  total1 = total1.add(totalMaintenanceBills(1));
  total1 = total1.subtract(totalMaintenanceIncomes(1));
  total1 = total1.multiply(otherPercent(ben.maintenance_percentage));
  total1 = total1.add(totalGifts(1));

  let total2 = numeral(0);
  total2 = total2.add(totalMaintenancePurchases(2));
  total2 = total2.add(totalMaintenanceBills(2));
  total2 = total2.subtract(totalMaintenanceIncomes(2));
  total2 = total2.multiply(otherPercent(melinda.maintenance_percentage));

  total2 = total2.add(totalGifts(2));

  total = numeral(total1.value() - total2.value());

  return total;
});

const userMaintenanceTransactions = computed(() => (userId, type) => {
  return transactionStore.transactions.filter((transaction) => {
    return (
      transaction.type === type &&
      transaction.user_id === userId &&
      transaction.tag === "MAINTENANCE"
    );
  });
});

const userGiftTransactions = computed(() => (userId) => {
  return transactionStore.transactions.filter((transaction) => {
    return transaction.user_id === userId && transaction.recipient_id;
  });
});

function otherUser(value) {
  if (value == "Ben") {
    return "Melinda";
  } else {
    return "Ben";
  }
}

function owedToTextBased(value, usernameValue) {
  if (value > 0) {
    return transactionStore.users.filter(function (user) {
      return user.name == usernameValue;
    })[0].name;
  } else {
    return otherUser(
      transactionStore.users.filter(function (user) {
        return user.name == usernameValue;
      })[0].name
    );
  }
}

function formatMoney(value) {
  return numeral(value).format("$0,0.00");
}

function totalOfTransactions(transactions, tag) {
  let localTransactions = JSON.parse(JSON.stringify(transactions));

  if (localTransactions) {
    //If this is a bill and there is a recurrent amount use the recurrent amount for the month, otherwise use the transaction amount
    var total = 0;
    localTransactions.forEach(function (obj) {
      if (
        obj.RecurrenceId &&
        obj.RecurrentAmounts &&
        obj.RecurrentAmounts.id &&
        obj.amountVaries &&
        obj.RecurrentAmounts.amount
      ) {
        obj.amount = obj.RecurrentAmounts.amount;
      }
    });

    if (tag == "MAINTENANCE") {
      total = localTransactions
        .filter(({ localTag }) => localTag == "MAINTENANCE")
        .reduce((acc, curr) => acc.add(curr.amount), numeral(0));
    } else {
      total = localTransactions.reduce(
        (acc, curr) => acc.add(curr.amount),
        numeral(0)
      );
    }
  }
  return total._value;
}

function otherPercent(percentageOwed) {
  let otherPercent = numeral(percentageOwed);
  otherPercent = otherPercent.divide(100);
  otherPercent = numeral(1).subtract(otherPercent.value());

  return otherPercent._value;
}
</script>
<style></style>
