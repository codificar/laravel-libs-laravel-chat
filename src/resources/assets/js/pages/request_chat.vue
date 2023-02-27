<script>
import ChatComponent from './chat_component.vue';
import HelpChatComponent from './help_chat.vue';
export default {
    props: [
        'environment',
        'laravel_echo_port',
        'Request',
        'RequestPoints',
        'User',
        'Institution',
        'mapsApiKey',
        'logo',
        'currencySymbol',
        'help',
        'message',
        'admin',
        'ConversationId',
    ],
    data() {
        return {
            request: JSON.parse(this.Request),
            request_points: JSON.parse(this.RequestPoints),
            user: JSON.parse(this.User),
            institution: this.Institution ? JSON.parse(this.Institution) : '',
        };
    },
    components: {
        ChatComponent,
        HelpChatComponent,
    },
    methods: {
        acceptOffer() {
            this.$swal({
                title: 'Accept Offer?',
                type: 'warning',
                showCancelButton: true,
                confirmButtonText: this.trans('yes'),
                cancelButtonText: this.trans('no'),
            }).then((result) => {});
        },
    },
};
</script>
<template>
    <div class="row main-content">
        <div class="col col-md-4 hide-small">
            <div class="card card-outline-info left-panel">
                <div class="card-header">
                    <div class="row align-items-center">
                        <div class="col-sm-12">
                            <h3 class="card-title text-white m-b-0">
                                {{ trans('laravelchat.chat_request') }}
                            </h3>
                        </div>
                    </div>
                </div>
                <div class="card-block">
                    <div class="col-md-12 col-sm-12" style="overflow-x: hidden">
                        <table class="table">
                            <tbody>
                                <tr
                                    v-for="(point, index) in request_points"
                                    :key="point.id"
                                >
                                    <td v-if="index == 0">
                                        {{ trans('laravelchat.origin') }}
                                    </td>
                                    <td
                                        v-else-if="
                                            index != request_points.length - 1
                                        "
                                    >
                                        {{
                                            trans('laravelchat.point') +
                                            ' ' +
                                            String.fromCharCode(65 + index)
                                        }}
                                    </td>
                                    <td v-else>
                                        {{ trans('laravelchat.destination') }}
                                    </td>
                                    <td
                                        class="text-overflow"
                                        :data-text="point.address"
                                    >
                                        {{ point.address }}
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        {{
                                            trans(
                                                'laravelchat.estimate_distance'
                                            )
                                        }}
                                    </td>
                                    <td>
                                        {{
                                            number_format(
                                                request.estimate_distance,
                                                2,
                                                ',',
                                                ''
                                            )
                                        }}
                                        Km
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        {{ trans('laravelchat.estimate_time') }}
                                    </td>
                                    <td>
                                        {{
                                            number_format(
                                                request.estimate_time,
                                                2,
                                                ',',
                                                ''
                                            )
                                        }}
                                        min
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col col-md-8 full-panel" v-if="!help">
            <ChatComponent
                :laravel_echo_port="laravel_echo_port"
                :request="Request"
                :user="User"
                :environment="environment"
                :channel="request.id"
                :logo="logo"
                :admin="admin"
                :institution="institution"
                :conversation-id="ConversationId"
            >
            </ChatComponent>
        </div>

        <div class="col col-md-8 full-panel" v-else>
            <HelpChatComponent
                :laravel_echo_port="laravel_echo_port"
                :user="User"
                :environment="environment"
                :channel="request.id"
                :logo="logo"
                :message="message"
                :admin="admin"
                :conversation-id="ConversationId"
            >
            </HelpChatComponent>
        </div>
    </div>
</template>

<style>
.main-content {
    height: 84vh;
}

.left-panel {
    height: 100%;
    margin-right: 15px;
}

@media (max-width: 720px) {
    .hide-small {
        display: none !important;
    }

    .footer {
        display: none !important;
    }

    .main-content {
        height: 84vh;
    }

    .page-wrapper {
        height: 100vh;
    }
}
</style>
