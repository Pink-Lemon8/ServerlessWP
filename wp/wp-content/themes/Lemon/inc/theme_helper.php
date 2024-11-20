<?php
function add_cart_overlay_alert($title = "", $content = '', $post_id = -1, $type = "alert")
{
	?>
	<div aria-live="assertive" id="<?= $type ?>"
		class="pointer-events-none fixed inset-0 flex items-end px-4 py-6 sm:items-start sm:p-6 z-50">
		<div class="flex w-full flex-col items-center space-y-4 sm:items-end">
			<div
				class="pointer-events-auto flex w-full max-w-md rounded-lg bg-white shadow-lg ring-1 ring-black ring-opacity-5">
				<div class="w-0 flex-1 p-4">
					<div class="flex items-start">
						<div class="flex-shrink-0 pt-0.5">
							<?= get_the_post_thumbnail($post_id, 'small', array('class' => 'h-10 w-10 rounded-full')) ?>
						</div>
						<div class="ml-3 w-0 flex-1">
							<p class="text-sm font-medium text-gray-900">
								<?= $title ?>
							</p>
							<p class="mt-1 text-sm text-gray-500">
								<?= $content ?>
							</p>
						</div>
					</div>
				</div>
				<div class="flex border-l border-gray-200">
					<button type="button" onclick="alertRemove('<?= $type ?>');"
						class="flex w-full items-center justify-center rounded-none rounded-r-lg border border-transparent p-4 text-sm font-medium text-black-600 hover:text-black-500 focus:outline-none">X</button>
				</div>

			</div>
		</div>
	</div>
	<script> change_cart_text('<?=Cart::getItemCount() ?>'); </script>
	<?php
}

function alert($text = '',$color='red',$icon="fail")
{
	?>
	<div class="rounded-md bg-<?= $color ?>-50 p-4 my-10">
        <div class="flex">
		<div class="flex-shrink-0">
		<?php if($icon == "info"): ?>
            <svg class="h-5 w-5 text-<?= $color ?>-400" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
              <path fill-rule="evenodd"
                d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a.75.75 0 000 1.5h.253a.25.25 0 01.244.304l-.459 2.066A1.75 1.75 0 0010.747 15H11a.75.75 0 000-1.5h-.253a.25.25 0 01-.244-.304l.459-2.066A1.75 1.75 0 009.253 9H9z"
                clip-rule="evenodd" />
            </svg>
			<?php 
			elseif($icon == "success"):
				?>
				<svg class="h-5 w-5 text-<?= $color ?>-400" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                <path fill-rule="evenodd"
                  d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z"
                  clip-rule="evenodd"></path>
              </svg>
			  <?php

			elseif($icon == "fail"):
				?>
				<svg class="h-5 w-5 text-<?= $color ?>-400" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
				<path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.28 7.22a.75.75 0 00-1.06 1.06L8.94 10l-1.72 1.72a.75.75 0 101.06 1.06L10 11.06l1.72 1.72a.75.75 0 101.06-1.06L11.06 10l1.72-1.72a.75.75 0 00-1.06-1.06L10 8.94 8.28 7.22z" clip-rule="evenodd"></path>
           		</svg>
			  <?php
			endif;
			?>
        </div>
          <div class="ml-3 flex-1 md:flex md:justify-between">
            <p class="text-sm  font-medium text-<?= $color ?>-800">
              <?= $text ?>
            </p>
          </div>
        </div>
      </div>
	<?php
}

function alert_and_link($text = '',$url="",$url_text="",$color='red',$icon="fail")
{
	?>
	<div class="rounded-md bg-<?= $color ?>-50 p-4">
      <div class="flex">
        <div class="flex-shrink-0">
		<?php if($icon == "info"): ?>
            <svg class="h-5 w-5 text-<?= $color ?>-400" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
              <path fill-rule="evenodd"
                d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a.75.75 0 000 1.5h.253a.25.25 0 01.244.304l-.459 2.066A1.75 1.75 0 0010.747 15H11a.75.75 0 000-1.5h-.253a.25.25 0 01-.244-.304l.459-2.066A1.75 1.75 0 009.253 9H9z"
                clip-rule="evenodd" />
            </svg>
			<?php 
			elseif($icon == "success"):
				?>
				<svg class="h-5 w-5 text-<?= $color ?>-400" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                <path fill-rule="evenodd"
                  d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z"
                  clip-rule="evenodd"></path>
              </svg>
			  <?php

			elseif($icon == "fail"):
				?>
				<svg class="h-5 w-5 text-<?= $color ?>-400" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
				<path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.28 7.22a.75.75 0 00-1.06 1.06L8.94 10l-1.72 1.72a.75.75 0 101.06 1.06L10 11.06l1.72 1.72a.75.75 0 101.06-1.06L11.06 10l1.72-1.72a.75.75 0 00-1.06-1.06L10 8.94 8.28 7.22z" clip-rule="evenodd"></path>
           		</svg>
			  <?php
			endif;
			?>
        </div>
        <div class="ml-3 flex-1 md:flex md:justify-between">
          <p class="text-sm font-medium text-<?= $color ?>-800">
		  <?= $text ?>
			</p>
          <p class="mt-3 text-sm md:ml-6 md:mt-0">
            <a href="<?= $url ?>"
              class="whitespace-nowrap font-medium text-<?= $color ?>-800 hover:text-<?= $color ?>-600  focus:text-<?= $color ?>-700">
              <?= $url_text ?>
            </a>
          </p>
        </div>
      </div>
    </div>
	<?php
}

function alert_and_links($text = '',$title = "",$urls=[],$color='red',$icon="fail"){
	?>
	<div class="rounded-md bg-<?= $color ?>-50 p-4 my-20">
        <div class="flex">
			<div class="flex-shrink-0">
			<?php if($icon == "info"): ?>
				<svg class="h-5 w-5 text-<?= $color ?>-400" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
				<path fill-rule="evenodd"
					d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a.75.75 0 000 1.5h.253a.25.25 0 01.244.304l-.459 2.066A1.75 1.75 0 0010.747 15H11a.75.75 0 000-1.5h-.253a.25.25 0 01-.244-.304l.459-2.066A1.75 1.75 0 009.253 9H9z"
					clip-rule="evenodd" />
				</svg>
				<?php 
				elseif($icon == "success"):
					?>
					<svg class="h-5 w-5 text-<?= $color ?>-400" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
					<path fill-rule="evenodd"
					d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z"
					clip-rule="evenodd"></path>
				</svg>
				<?php

				elseif($icon == "fail"):
					?>
					<svg class="h-5 w-5 text-<?= $color ?>-400" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
					<path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.28 7.22a.75.75 0 00-1.06 1.06L8.94 10l-1.72 1.72a.75.75 0 101.06 1.06L10 11.06l1.72 1.72a.75.75 0 101.06-1.06L11.06 10l1.72-1.72a.75.75 0 00-1.06-1.06L10 8.94 8.28 7.22z" clip-rule="evenodd"></path>
					</svg>
				<?php
				endif;
				?>
			</div>
            <div class="ml-3">
                <h3 class="text-sm font-medium text-<?= $color ?>-800"><?= $title ?></h3>
                <div class="mt-2 text-sm font-medium text-<?= $color ?>-700">
                    <p><?=$text ?></p>
                </div>
                <div class="mt-4">
                    <div class="-mx-2 -my-1.5 flex">
						<?php foreach ($urls as $key => $value): ?>
                        <button type="button"
                            class="rounded-md bg-<?= $color ?>-50 px-2 py-1.5 text-sm font-medium text-<?= $color ?>-800 hover:bg-<?= $color ?>-100 focus:outline-none">
                            <a href="<?= $value ?>" class="text-<?= $color ?>-800 hover:text-<?= $color ?>-800 focus:text-<?= $color ?>-800"><?= $key ?></a>
                        </button>
                       <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
	<?php
}
function template_loading($show = false){
?>
	<div id="pl_loading" class="relative z-50 <?= $show ? "" : "hidden" ?>" aria-labelledby="modal-title" role="dialog" aria-modal="true">
		<div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"></div>
			<div class="fixed inset-0 z-50 overflow-y-auto">
				<div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
				<div class="load-3">
					<p class="text-white animate-pulse">Loading</p>
					<div class="line bg-[<?= MAIN_COLOR; ?>]"></div>
					<div class="line bg-[<?= MAIN_COLOR; ?>]"></div>
					<div class="line bg-[<?= MAIN_COLOR; ?>]"></div>
				</div>
			</div>
		</div>
	</div>
<?php
}
?>