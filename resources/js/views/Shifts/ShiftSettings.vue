<template>
    <div>
        <v-card>
            <v-card-title>
                Настройки смен
            </v-card-title>
            <v-card-text>
                <v-simple-table v-slot:default :dense="false">
                    <thead>
                    <tr>
                        <th>Магазин</th>
                        <th>Оплата за смену</th>
                        <th>Процент от продаж (день)</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr v-for="(tax, key) of shiftTaxes">
                        <td>{{ tax.store.name }}</td>
                        <td>
                            <v-text-field
                                label="Смена"
                                v-model.number="tax.shift_tax"
                                type="number"></v-text-field>
                        </td>
                        <td>
                            <div class="d-flex align-center justify-space-between">
                                <ul v-if="tax.shift_rules && tax.shift_rules.length > 0">
                                    <li v-for="(rule, salaryKey) of tax.shift_rules" :key="`salary-rule-${salaryKey}`" class="d-flex align-center">
                                        <v-text-field
                                            label="Порог"
                                            v-model="rule.threshold"
                                        />
                                        <v-text-field
                                            label="%, зарплаты"
                                            v-model="rule.value"
                                        />
                                        <v-btn icon color="error" @click="deleteSalaryRule(key, salaryKey)">
                                            <v-icon>mdi-close</v-icon>
                                        </v-btn>
                                    </li>
                                </ul>
                                <p v-else>Правила не установлены</p>
                                <v-btn icon color="success" @click="addSalaryRule(key)">
                                    <v-icon>
                                        mdi-plus
                                    </v-icon>
                                </v-btn>
                            </div>
                        </td>
                    </tr>
                    </tbody>
                </v-simple-table>
                <v-btn color="success" class="mt-5" @click="save">
                    Сохранить <v-icon>mdi-check</v-icon>
                </v-btn>
            </v-card-text>
        </v-card>
    </div>
</template>

<script>
    import { mapState, mapActions } from 'vuex';
    import { getShiftTaxes } from '@/api/v2/shifts';

    export default {
        data: () => ({
            shiftTaxes: [],
        }),
        async mounted() {
            const { data } = await getShiftTaxes();
            this.shiftTaxes = data;
        },
        methods: {
            async save() {
                const taxes = this.shiftTaxes.map(s => ({
                    shift_tax: s.shift_tax,
                    shift_rules: s.shift_rules,
                    store_id: s.store.id,
                }))

                try {
                    await this.SAVE_SHIFT_TAXES(taxes);
                    this.$toast.success('Настройки успешно обновлены');
                } catch (e) {
                    this.$toast.error('При обновлении произошла ошибка')
                }

            },
            addSalaryRule(key) {
                console.log(key);
                const needle = this.shiftTaxes[key];
                needle.shift_rules = needle.shift_rules ? [...needle.shift_rules, {threshold: 0, value: 0}] : [{threshold: 0, value: 0}];
                this.$set(this.shiftTaxes, key, needle)
            },
            deleteSalaryRule(typeKey, ruleKey) {
                const needle = this.shiftTaxes[typeKey];
                needle.shift_rules.splice(ruleKey, 1);
                this.$set(this.shiftTaxes, typeKey, needle)
            },
            ...mapActions(['SAVE_SHIFT_TAXES'])
        },
    }
</script>

<style scoped>

</style>
