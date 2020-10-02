<template>
    <div>
        <div class="tab-content">
            <div class="col-lg-12">
                <div class="card card-outline-info">
                    <div class="card-header">
                        <h4 class="m-b-0 text-white">{{ trans('laravelchat.filters') }}</h4>
                    </div>

                    <div class="card-block">
                        <div class="row">
                            <div class="col-md-4 col-sm-12">
                                <!--Request-->
                                <div class="form-group">
                                    <label for="request_id" class="control-label">{{ trans('laravelchat.request_id') }}</label>                                  
                                    <input 
                                        class="form-control" 
                                        maxlenght="255" 
                                        auto-focus 
                                        type="number"
                                        :placeholder="trans('laravelchat.request_id')"
                                        v-model="filter.request_id" />
                                </div>
                            </div>

                            <div class="col-md-4 col-sm-12">
                                <!--User-->
                                <div class="form-group">
                                    <label for="user_id" class="control-label">
                                        {{ trans('laravelchat.user') }}
                                    </label>                                  
                                    <autocomplete
                                        source="/admin/searchreferral?type=0&name="
                                        method="get"
                                        input-class="form-control"
                                        :placeholder="trans('laravelchat.user')"
                                        results-property="referrals"
                                        :results-display="renderAutocompleteResults"
                                        @selected="selectUser"
                                        @clear="clearUser"
                                    ></autocomplete>
                                </div>
                            </div>

                            <div class="col-md-4 col-sm-12">
                                <!--Provider-->
                                <div class="form-group">
                                    <label for="provider_id" class="control-label">
                                        {{ trans('laravelchat.provider') }}
                                    </label>                                  
                                    <autocomplete
                                        source="/admin/searchreferral?type=1&name="
                                        method="get"
                                        input-class="form-control"
                                        name="institution_id"
                                        :placeholder="trans('laravelchat.provider')"
                                        results-property="referrals"
                                        :results-display="renderAutocompleteResults"
                                        @selected="selectProvider"
                                        @clear="clearProvider"
                                    ></autocomplete>
                                </div>
                            </div>
                        </div>

                        <!-- Action -->
                        <div class="form-group">
                            <div class="pull-right">
                                <div class="col-md-6 col-md-offset-4">
                                    <button @click="fetch" type="button" class="btn btn-success">
                                        <i class="fa fa-search"></i> 
                                        {{ trans('laravelchat.filter') }}
                                    </button>               
                                </div>
                            </div>
                        </div> 
                    </div>
                </div>
            </div>
        </div>

        <div class="tab-content">
            <div class="col-lg-12">
                <div class="card card-outline-info">            
                    <div class="card-block">
                        <pagination 
                            :data="request_help" 
                            @pagination-change-page="fetch" 
                        >
                        </pagination>

                        <table class="table">
                            <tr>
                                <th>{{ trans('laravelchat.request_id') }}</th>
                                <th>{{ trans('laravelchat.user') }}</th>
                                <th>{{ trans('laravelchat.provider') }}</th>
                                <th>{{ trans('laravelchat.author') }}</th>
                                <th>{{ trans('laravelchat.actions') }}</th>
                            </tr>
                            <tr 
                                v-for="(item, index) in request_help.data"
                                :key="index"
                            >
                                <td>{{ item.request_id }}</td>
                                <td>{{ item.user_name }}</td>
                                <td>{{ item.provider_name }}</td>
                                <td>{{ trans(`laravelchat.${item.author}`) }}</td>
                                <td>
                                    <div class="dropdown">
                                        <button 
                                            class="btn btn-info dropdown-toggle"
                                            type="button" 
                                            id="dropdownMenu1" 
                                            data-toggle="dropdown"
                                        >
                                            {{ trans('laravelchat.actions') }}
                                            <span class="caret"></span>
                                        </button>

                                        <div 
                                            class="dropdown-menu dropdown-menu-right" 
                                            role="menu" 
                                            aria-labelledby="dropdownMenu1"
                                        >
                                            <!-- Chat -->
                                            <a 
                                                class="dropdown-item" 
                                                tabindex="-1"
                                                target="_blank"
                                                :href="'/admin/libs/help/' + item.id"
                                            >
                                                {{ trans('laravelchat.open_chat') }}
                                            </a>

                                        </div>
                                    </div>
                                </td>
                            </tr>
                        </table>

                        <pagination 
                            :data="request_help" 
                            @pagination-change-page="fetch" 
                        >
                        </pagination>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import autocomplete from 'vuejs-auto-complete';

export default {
    components: {
        autocomplete
    },

    data() {
        return {
            filter: {
                request_id: '',
                user_id: '',
                provider_id: ''
            },
            request_help: {}
        }
    },

    methods: {
        fetch(page = 1) {
            this.$axios.get('/api/libs/help_list', {
                params: {
                    page: isNaN(page) ? 1 : page,
                    filter: this.filter
                }
            })
            .then(res => {
                const {data} = res;
                
                if (data.success) {
                    this.request_help = data.request_help.request_help;
                }
            });

        },

        renderAutocompleteResults(result) {
            return `${result.first_name} ${result.last_name}`;
        },

        selectUser(result) {
            const { selectedObject } = result;
			this.filter.user_id = selectedObject.id;

        },

        clearUser() {
            this.filter.user_id = '';
        },

        selectProvider(result) {
            const { selectedObject } = result;
			this.filter.provider_id = selectedObject.id;

        },

        clearProvider() {
            this.filter.provider_id = '';
        }
    },

    mounted() {
        this.fetch();
    }
} 
</script>

<style>

</style>