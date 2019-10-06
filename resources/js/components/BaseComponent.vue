<template>

    <div class="card">
        <div class="card-header">
            <div class="btn-group float-right" role="group" aria-label="Basic example">
                <input type="file" name="file-import" id="file-import" class="input-file" @change="importCsv()">
                <label for="file-import" class="btn btn-outline-dark">
                    <span>Импортировать</span>
                </label>
                <button type="button" class="btn btn-outline-dark">Экспортировать</button>
                <button type="button" class="btn btn-outline-dark" @click="calculate()">Пересчитать</button>
            </div>
        </div>
        <div class="card-body">
            <div v-for="(record, key) in collection" class="row p-1">
                <div class="col-sm-7">
                    <div class="form-control content-line">
                        {{record.text}}
                    </div>
                </div>
                <div class="col-sm-5">
                    <div class="form-control">
                        <input typeof="text" class="content-line" type="text" v-model="record.topic">
                        <div v-if="record.source == 'predicted'" class="btn-group-sm float-right" role="group" aria-label="Basic example">
                            <button type="button" class="btn btn-outline-dark btn-sm" @click="accept(key)">Принять</button>
                            <button type="button" class="btn btn-outline-dark btn-sm" @click="reject(key)">Отклонить</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="card-footer">
            <ul class="pagination float-right">
                <li class="page-item" :class="{ disabled: isMinPage }">
                    <span class="page-link" aria-hidden="true" @click.prevent="setPage(curPage - 1)">‹</span>
                </li>
                <li v-for="page in pagination.pages" class="page-item" :class="{ active: page == curPage }" v-if="((page >= (curPage - 2)) && (page <= (curPage + 2)))">
                    <span class="page-link" @click.prevent="setPage(page)">{{ page }}</span>
                </li>
                <li class="page-item" :class="{ disabled: isMaxPage }">
                    <span class="page-link" aria-hidden="true" @click.prevent="setPage(curPage + 1)">›</span>
                </li>
            </ul>
        </div>

        <wait-component v-if="waitProcess"></wait-component>

    </div>

</template>

<script>
    export default {
        data: function () {
            return {
                records: [],
                perPage: 10,
                pagination: {},
                curPage: 1,
                totalPage: 0,
                waitProcess: false,
            }
        },
        computed: {
            collection() {
                return this.paginate(this.records);
            },
            isActive: function (page) {
                return  (this.curPage == page) ? true : false;
            },
            isMinPage() {
                return (this.curPage <= 1) ? true : false;
            },
            isMaxPage() {
                return (this.curPage >= this.totalPage) ? true : false;
            },
        },
        mounted() {
            this.getRecords();
        },
        methods: {
            getRecords() {
                axios.get('/get-records').then(response => {
                    this.records = response.data;
                    this.totalPage = this.records.length / this.perPage;
                    this.setPage(1);
                }).catch(error => {
                    console.error(error.response);
                })
            },
            // Загрузка данных из csv файла
            importCsv() {
                let formData = new FormData();
                let fileField = document.getElementById('file-import');

                formData.append('file', fileField.files[0]);

                axios.post('/import-csv', formData, {headers: {'Content-Type': 'multipart/form-data'}})
                    .then(response => {
                        this.records = response.data;
                        this.totalPage = this.records.length / this.perPage;
                        this.setPage(1);
                    }).catch(error => {
                    console.error(error.response);
                });
            },
            setPage(page) {
                this.curPage = page;
                this.pagination = this.paginator(this.records.length, page);
            },
            paginate(data) {
                return _.slice(data, this.pagination.startIndex, this.pagination.endIndex + 1);
            },
            // Функция пагинации
            paginator(totalItems, currentPage) {
                let startIndex = (currentPage - 1) * this.perPage,
                    endIndex = Math.min(startIndex + this.perPage - 1, totalItems - 1);
                return {
                    currentPage: currentPage,
                    startIndex: startIndex,
                    endIndex: endIndex,
                    pages: _.range(1, Math.ceil(totalItems / this.perPage) + 1)
                };
            },
            // Пересчет
            calculate() {
                this.waitProcess = true;
                axios.post('/calculate-records',{
                    data: JSON.stringify(this.records),
                }).then((response) => {
                    this.records = response.data;
                    this.totalPage = this.records.length / this.perPage;
                    this.setPage(1);
                    this.waitProcess = false;
                }).catch(error => {
                    this.waitProcess = false;
                    console.error(error.response);
                });
            },
            // Принять предложенный вариант
            accept(key) {
                axios.post('/save-record',{
                    data: JSON.stringify(this.records[key]),
                }).then((response) => {
                    this.records[key].source = '';
                }).catch(error => {
                    console.error(error.response);
                });
            },
            // Отклонить предложенный вариант
            reject(key) {
                this.records[key].source = '';
                this.records[key].topic = '';
            }
        }
    }
</script>