<template>
    <div>
        <t-alert
            variant="danger"
            :show="error"
            class="mt-6"
        >
            Something goes wrong. Please double check the fields.
        </t-alert>
        <div class="flex mt-4">
            <div class="flex-none w-1/3 pr-4">
                <div>
                    <label>Event:</label>
                    <div class="mt-2">
                        <t-input v-model="event.name" />
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4 mt-4">
                    <div>
                        <label>From:</label>
                        <div class="mt-2">
                            <t-datepicker v-model="event.from" placeholder="From" />
                        </div>
                    </div>
                    <div>
                        <label>To:</label>
                        <div class="mt-2">
                            <t-datepicker
                                v-model="event.to" :disabled="!event.from"
                                :min-date="event.from"
                                placeholder="To"
                            />
                            <div class="text-xs font-italic mt-1">Note: disabled if from is empty</div>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-7 gap-4 mt-4">
                    <div v-for="(day, index) in daysOfWeek" :key="index">
                        <label class="flex items-center">
                            <span class="mr-1 text-sm">{{ day.label }}</span>
                            <t-checkbox v-model="day.model" />
                        </label>
                    </div>
                </div>

                <t-button class="mt-4" @click="save()">Save</t-button>
            </div>
            <div class="flex-grow border-b border-gray-600">
                <div>
                    Year and month:
                    <t-datepicker
                        v-model="monthYear"
                        initial-view="month"
                        date-format="Y-m"
                        user-format="Y F"
                    />
                </div>
                <div class="mt-6">
                    <div
                        v-for="date in dates" :key="date.key"
                        class="flex flex-row border-l border-r border-t border-gray-600 px-3 py-1"
                        :class="getEventClass(date.events)"
                    >
                        <div class="flex-none pr-4">
                            {{ date.day }} {{ date.dayOfWeek }}
                        </div>
                        <div class="flex-grow text-center">
                            <template v-if="date.events.length > 0">
                                {{ date.events[0].name }}
                            </template>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>

import moment from 'moment'

export default {
    data: () => ({
        error: false,
        monthYear: null,
        event: {
            name: null,
            from: null,
            to: null,
            days: []
        },
        daysOfWeek: [
            {
                label: 'Sun',
                value: 0,
                model: false
            },
            {
                label: 'Mon',
                value: 1,
                model: false
            },
            {
                label: 'Tue',
                value: 2,
                model: false
            },
            {
                label: 'Wed',
                value: 3,
                model: false
            },
            {
                label: 'Thu',
                value: 4,
                model: false
            },
            {
                label: 'Fri',
                value: 5,
                model: false
            },
            {
                label: 'Sat',
                value: 6,
                model: false
            }
        ],
        dates: [],
        events: []
    }),
    mounted() {
        this.monthYear = moment().format('YYYY-MM')
        this.listDates()
    },
    watch: {
        monthYear() {
            this.listDates()
        }
    },
    methods: {
        listDates() {
            var current = moment(this.monthYear, 'YYYY-MM')
            this.dates = this.daysOfMonth(current.format('YYYY'), current.format('M'))
            this.getEvents()
        },
        daysOfMonth(year, month) {
            var monthDate = moment(year+'-'+month, 'YYYY-MM')
            var daysInMonth = monthDate.daysInMonth()
            var days = []

            var day = 1
            while(day <= daysInMonth) { 
                var current = moment(`${year}-${month}-${day}`, 'YYYY-MM-D')
                var dayOfWeek = current.format('ddd')
                days.push({ day, dayOfWeek, events: [], realDate: current  })
                day++;
            }

            return days;
        },
        save() {
            var vm = this
            vm.attachDaysOfWeek()
            var data = { ...vm.event }
            axios.post('/api/events', data)
                .then((response) => {
                    if (response.status == 200) {
                        vm.getEvents()
                        vm.clearFields()
                    }
                    vm.error = false
                }).catch(() => {
                    vm.error = true
                })
        },
        getEvents() {
            var vm = this
            axios.get('/api/events')
                .then((response) => {
                    if (response.status == 200) {
                        var events = response.data.data

                        if (events)
                            vm.events = events

                        vm.clearDates()
                        vm.attachEvents()
                    }
                })
        },
        attachEvents() {
            var vm = this
            var event = vm.events.length > 0 ? vm.events[0] : []
            var from = moment(event.from, 'YYYY-MM-DD')
            var to = moment(event.to, 'YYYY-MM-DD')

            vm.dates = _.map(vm.dates, date => {
                if (date.realDate.isBetween(from, to) || date.realDate.isSame(from) || date.realDate.isSame(to)) {
                    var index = _.findIndex(event.days, { 'day': date.realDate.day() })
                    if (event.days.length === 0 || (event.days.length > 0  && index !== -1))
                        date.events.push(event)
                }
                return date
            })
        },
        getEventClass(events) {
            return {
                'bg-indigo-300': events.length > 0
            }
        },
        clearDates() {
            this.dates = _.map(this.dates, date => {
                date.events = []
                return date
            })
        },
        attachDaysOfWeek() {
            var days = []
            this.daysOfWeek.forEach(dayOfWeek => {
                if (dayOfWeek.model)
                    days.push(dayOfWeek.value)
            })
            this.event.days = days
        },
        clearFields() {
            this.event.name = null
            this.event.from = null
            this.event.to = null
            this.event.days = []

            this.daysOfWeek = this.daysOfWeek.map(dayOfWeek => {
                dayOfWeek.model = false
                return dayOfWeek
            })
        }
    }
}
</script>