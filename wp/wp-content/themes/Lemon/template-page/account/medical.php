<div id="medical-questionnaire" class="mt-20" style="display: none;">
    <div class="px-4 sm:px-0">
      <h3 class="text-base font-semibold leading-7 text-gray-900">Update Medical Questionnaire:</h3>
      <p class="mt-1 text-sm leading-6 text-gray-500">If you answer 'yes' to any of the following questions, please provide details.</p>
    </div>
    <div class="mt-6 border-t border-gray-100">
      <form method="post" class="medical-questionnaire-form" data-abide="atia6d-abide" data-validate-on-blur="true" data-live-validate="true" novalidate="">

      <dl class="divide-y divide-gray-100">
        <div class="bg-gray-50 px-4 py-6 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-3">
          <dt class="text-sm font-medium leading-6 text-gray-900">Are you currently taking any vitamins, minerals or herbs?</dt>
          <dd class="mt-1 text-sm leading-6 text-gray-700 sm:col-span-2 sm:mt-0">
          <input type="radio" id="1_Yes" name="response[1]" value="Yes" required="" aria-describedby="orwi28-abide-error"> 
            <label for="1_Yes">Yes</label>
            <input type="radio" id="1_No" name="response[1]" value="No" required="" aria-describedby="orwi28-abide-error"> 
            <label for="1_No">No</label>
            <textarea name="comment[1]" data-question-id="1" placeholder="If 'Yes', please provide details." required="" >
            </textarea>
          </dd>
        </div>

        <div class="bg-white px-4 py-6 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-3">
          <dt class="text-sm font-medium leading-6 text-gray-900">Any medical condition or history that our Pharmacists should be aware of?</dt>
          <dd class="mt-1 text-sm leading-6 text-gray-700 sm:col-span-2 sm:mt-0">
          <div class="question"></div><input type="radio" id="2_Yes" name="response[2]" value="Yes" required="" aria-describedby="6ddv45-abide-error"> 
              <label for="2_Yes">Yes</label>
              <input type="radio" id="2_No" name="response[2]" value="No" required="" aria-describedby="6ddv45-abide-error"> 
              <label for="2_No">No</label>
              <textarea name="comment[2]" data-question-id="2" placeholder="If 'Yes', please provide details." required="" >
              </textarea>
          </dd>
        </div>

        <div class="bg-white px-4 py-6 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-3">
          
        </div>

      </dl>
      </form>
    </div>
</div>