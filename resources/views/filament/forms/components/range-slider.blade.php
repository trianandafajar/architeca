<x-dynamic-component
    :component="$getFieldWrapperView()"
    :field="$field"
>
    <div
        x-data="{
            state: $wire.entangle('{{ $getStatePath() }}')
        }"
        class="fi-slider"
    >
        <div class="fi-slider-container">
            {{-- Custom thumb + realtime value --}}
            <div
                class="fi-slider-value"
                :style="`
                    left: calc(
                        ${(Number(state) || 0) / 100} * (100% - 40px)
                        + 20px
                    )
                `"
                x-text="`${state ?? 0}%`"
            ></div>

            <input
                type="range"
                min="0"
                max="100"
                step="1"
                x-model.number="state"
                class="fi-slider-input"
            />
        </div>

        {{-- Ticks --}}
        <div class="fi-slider-ticks">
            @for ($i = 0; $i <= 10; $i++)
                <span
                    @class([
                        'fi-slider-tick',
                        'fi-slider-tick-edge' => $i === 0 || $i === 10,
                    ])
                ></span>
            @endfor
        </div>

        {{-- Min / Max --}}
        <div class="fi-slider-labels">
            <span>0</span>
            <span>100</span>
        </div>
    </div>

    <style>
        .fi-slider {
            width: 100%;
            padding-top: 4px;
        }

        .fi-slider-container {
            position: relative;
            height: 30px;
            display: flex;
            align-items: center;
        }

        /*
        |--------------------------------------------------------------------------
        | Range
        |--------------------------------------------------------------------------
        */

        .fi-slider-input {
            appearance: none;
            -webkit-appearance: none;

            width: 100%;
            height: 8px;

            margin: 0;
            padding: 0;

            border-radius: 9999px;

            background: rgb(39 39 42);
            border: 1px solid rgb(63 63 70);

            cursor: pointer;
            outline: none;
        }

        /*
        |--------------------------------------------------------------------------
            | Native thumb made transparent
        |--------------------------------------------------------------------------
        */

        .fi-slider-input::-webkit-slider-thumb {
            appearance: none;
            -webkit-appearance: none;

            width: 40px;
            height: 26px;

            background: transparent;
            border: 0;

            cursor: grab;
        }

        .fi-slider-input::-moz-range-thumb {
            width: 40px;
            height: 26px;

            background: transparent;
            border: 0;

            cursor: grab;
        }

        .fi-slider-input:active::-webkit-slider-thumb {
            cursor: grabbing;
        }

        /*
        |--------------------------------------------------------------------------
        | Custom thumb
        |--------------------------------------------------------------------------
        */

        .fi-slider-value {
            position: absolute;
            top: 50%;

            transform: translate(-50%, -50%);

            min-width: 40px;
            height: 26px;

            padding: 0 7px;

            display: flex;
            align-items: center;
            justify-content: center;

            border-radius: 8px;

            background: rgb(82 82 91);
            border: 1px solid rgb(113 113 122);

            color: white;

            font-size: 11px;
            font-weight: 600;
            line-height: 1;

            pointer-events: none;

            box-shadow:
                0 1px 3px rgba(0, 0, 0, .25),
                inset 0 1px 0 rgba(255, 255, 255, .08);

            z-index: 2;
        }

        /*
        |--------------------------------------------------------------------------
        | Ticks
        |--------------------------------------------------------------------------
        */

        .fi-slider-ticks {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;

            padding: 0 1px;
            margin-top: 3px;
        }

        .fi-slider-tick {
            display: block;

            width: 2px;
            height: 6px;

            border-radius: 999px;

            background: rgb(161 161 170);
        }

        .fi-slider-tick-edge {
            height: 12px;
            background: rgb(113 113 122);
        }

        /*
        |--------------------------------------------------------------------------
        | 0 / 100
        |--------------------------------------------------------------------------
        */

        .fi-slider-labels {
            display: flex;
            justify-content: space-between;

            margin-top: 1px;

            color: rgb(113 113 122);

            font-size: 11px;
            line-height: 1;
        }

        /*
        |--------------------------------------------------------------------------
        | Light Mode
        |--------------------------------------------------------------------------
        */

        :not(.dark) .fi-slider-input {
            background: rgb(244 244 245);
            border-color: rgb(212 212 216);
        }

        :not(.dark) .fi-slider-value {
            background: white;
            border-color: rgb(212 212 216);

            color: rgb(39 39 42);

            box-shadow:
                0 1px 3px rgba(0, 0, 0, .12);
        }
    </style>
</x-dynamic-component>
