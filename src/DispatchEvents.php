<?php

namespace Aol\Atc;


final class DispatchEvents
{
	const PRE_DISPATCH  = 'dispatch.pre_dispatch';
	const POST_DISPATCH = 'dispatch.post_dispatch';
	const PRE_PRESENT   = 'dispatch.pre_present';
	const POST_PRESENT  = 'dispatch.post_present';
	const DISPATCH_ERROR= 'dispatch.error';
	const EARLY_EVENT   = 999;
	const LATE_EVENT    = -999;
}